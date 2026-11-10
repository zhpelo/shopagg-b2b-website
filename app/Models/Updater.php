<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Migrator;
use App\Services\Updates\GithubReleaseProvider;
use App\Services\Updates\ReleaseProvider;
use App\Services\Updates\ShopaggUpdateProvider;
use App\Services\Updates\UpdateEventReporter;
use App\Services\Updates\UpdateProviderUnavailable;
use App\Services\Updates\UpdateSecurityException;

/**
 * 程序更新管理器
 * 
 * 处理 SHOPAGG 官方更新源、GitHub 备用源、下载校验和安全安装。
 */
class Updater {
    
    /** 当前版本号兜底值（正常由 APP_VERSION 常量提供） */
    private const CURRENT_VERSION = '1.3.0';

    /** 更新包安全限制 */
    private const MAX_DOWNLOAD_BYTES = 268435456; // 256 MB
    private const MAX_ARCHIVE_ENTRIES = 5000;
    private const MAX_ARCHIVE_FILE_BYTES = 52428800; // 50 MB
    private const MAX_ARCHIVE_TOTAL_BYTES = 536870912; // 512 MB
    private const MAX_COMPRESSION_RATIO = 200;

    /** 更新器只允许访问官方更新服务和 GitHub 下载域名 */
    private const ALLOWED_DOWNLOAD_HOSTS = [
        'github.com',
        'api.github.com',
        'codeload.github.com',
        'objects.githubusercontent.com',
        'github-releases.githubusercontent.com',
        'update.shopagg.org',
    ];
    
    /** 更新包下载目录 */
    private string $downloadDir;
    
    /** 更新备份目录 */
    private string $backupDir;
    
    /** 更新日志文件 */
    private string $logFile;
    
    /** 迁移管理器 */
    private Migrator $migrator;

    private ReleaseProvider $officialProvider;
    private ReleaseProvider $githubProvider;
    private UpdateEventReporter $eventReporter;
    private string $activeSource = 'shopagg';
    private string $lastProviderError = '';
    
    public function __construct() {
        $this->downloadDir = APP_ROOT . '/storage/updates';
        $this->backupDir = APP_ROOT . '/storage/backups';
        $this->logFile = APP_ROOT . '/storage/update.log';
        $this->migrator = new Migrator();
        $this->officialProvider = new ShopaggUpdateProvider();
        $this->githubProvider = new GithubReleaseProvider();
        $this->eventReporter = new UpdateEventReporter();
        
        // 确保目录存在
        if (!is_dir($this->downloadDir)) {
            mkdir($this->downloadDir, 0755, true);
        }
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * 获取当前版本号
     */
    public function getCurrentVersion(): string {
        if (defined('APP_VERSION')) {
            return (string) APP_VERSION;
        }

        return self::CURRENT_VERSION;
    }
    
    /**
     * 获取最新的已签名 Release 信息
     * 
     * @return array|null 返回最新版本信息，失败返回 null
     */
    public function getLatestRelease(): ?array {
        $this->lastProviderError = '';
        try {
            $release = $this->officialProvider->latest();
            $this->activeSource = 'shopagg';
            return $release;
        } catch (UpdateProviderUnavailable $officialError) {
            try {
                $release = $this->githubProvider->latest();
                $this->activeSource = 'github';
                return $release;
            } catch (\Throwable $githubError) {
                $this->lastProviderError = $githubError->getMessage();
                return null;
            }
        } catch (UpdateSecurityException $securityError) {
            $this->lastProviderError = $securityError->getMessage();
            return null;
        }
    }
    
    /**
     * 获取所有 Release 列表（用于查看更新历史）
     * 
     * @param int $page 页码
     * @param int $perPage 每页数量
     * @return array 返回版本列表
     */
    public function getReleases(int $page = 1, int $perPage = 10): array {
        try {
            return $this->officialProvider->releases($page, $perPage);
        } catch (UpdateProviderUnavailable) {
            try {
                return $this->githubProvider->releases($page, $perPage);
            } catch (\Throwable) {
                return [];
            }
        } catch (UpdateSecurityException) {
            return [];
        }
    }
    
    /**
     * 检查是否有新版本
     * 
     * @return array 返回检查结果
     */
    public function checkUpdate(): array {
        $currentVersion = $this->getCurrentVersion();
        $latestRelease = $this->getLatestRelease();
        
        if ($latestRelease === null) {
            return [
                'success' => false,
                'message' => $this->lastProviderError !== '' ? $this->lastProviderError : '无法获取最新版本信息，请检查网络连接或稍后重试',
                'current_version' => $currentVersion,
                'latest_version' => null,
                'has_update' => false,
                'source' => $this->activeSource,
            ];
        }
        
        $latestVersion = $latestRelease['version'];
        $hasUpdate = version_compare($latestVersion, $currentVersion, '>');
        
        $this->eventReporter->report('update_check', $currentVersion, $latestVersion, $this->activeSource);
        return [
            'success' => true,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'has_update' => $hasUpdate,
            'source' => $this->activeSource,
            'sodium_available' => extension_loaded('sodium'),
            'release_info' => $latestRelease,
            'message' => $hasUpdate ? '发现新版本：' . $latestVersion : '当前已是最新版本',
        ];
    }
    
    /**
     * 下载更新包
     * 
     * @param string $version 版本号
     * @param string $downloadUrl 下载地址
     * @return array 返回下载结果
     */
    public function downloadUpdate(string $version, string $downloadUrl, ?string $expectedHash = null, ?int $expectedSize = null, string $source = 'shopagg'): array {
        if (!$this->isValidVersion($version) || !$this->isAllowedDownloadUrl($downloadUrl)) {
            return [
                'success' => false,
                'message' => '版本号或下载地址无效',
                'filename' => null,
            ];
        }

        $filename = 'update-' . $version . '.zip';
        $filepath = $this->downloadDir . '/' . $filename;
        $hashFile = $filepath . '.sha256';
        $metadataFile = $filepath . '.json';
        
        // 缓存包也必须通过下载时记录的哈希校验。
        if ($this->verifyPackageHash($filepath, $hashFile)
            && ($expectedHash === null || hash_equals(strtolower($expectedHash), strtolower((string)hash_file('sha256', $filepath))))
            && ($expectedSize === null || filesize($filepath) === $expectedSize)) {
            return [
                'success' => true,
                'message' => '更新包已存在并通过完整性校验',
                'filename' => $filename,
            ];
        }

        @unlink($filepath);
        @unlink($hashFile);
        @unlink($metadataFile);
        $startedAt = microtime(true);
        $this->eventReporter->report('download_started', $this->getCurrentVersion(), $version, $source);

        // 下载文件
        $result = $this->downloadFile($downloadUrl, $filepath);
        
        if (!$result) {
            $this->eventReporter->report('download_failed', $this->getCurrentVersion(), $version, $source, ['error_code' => 'network_error']);
            return [
                'success' => false,
                'message' => '下载更新包失败',
                'filename' => null,
            ];
        }

        $hash = is_array($result) ? ($result['sha256'] ?? false) : false;
        $size = filesize($filepath);
        if (!is_string($hash)
            || ($expectedHash !== null && !hash_equals(strtolower($expectedHash), strtolower($hash)))
            || ($expectedSize !== null && $size !== $expectedSize)) {
            @unlink($filepath);
            @unlink($hashFile);
            $this->eventReporter->report('download_failed', $this->getCurrentVersion(), $version, $source, ['error_code' => 'integrity_mismatch']);
            return ['success' => false, 'message' => '更新包大小或 SHA-256 与签名清单不一致', 'filename' => null];
        }
        if (file_put_contents($hashFile, $hash . "\n", LOCK_EX) === false) {
            @unlink($filepath);
            @unlink($hashFile);
            return [
                'success' => false,
                'message' => '无法记录更新包完整性信息',
                'filename' => null,
            ];
        }
        file_put_contents($metadataFile, json_encode(['source' => $source], JSON_UNESCAPED_SLASHES), LOCK_EX);
        
        $this->eventReporter->report('download_completed', $this->getCurrentVersion(), $version, $source, [
            'bytes' => (int)$size,
            'duration_ms' => (int)round((microtime(true) - $startedAt) * 1000),
        ]);
        return [
            'success' => true,
            'message' => '下载成功并已完成 SHA-256 完整性校验',
            'filename' => $filename,
        ];
    }
    
    /**
     * 从 GitHub Source Code (zip) 下载
     * 
     * @param string $version 版本号
     * @return array 返回下载结果
     */
    public function downloadSourceZip(string $version): array {
        if (!$this->isValidVersion($version)) {
            return ['success' => false, 'message' => '版本号格式无效'];
        }
        try {
            try {
                $release = $this->officialProvider->release($version);
                $this->activeSource = 'shopagg';
            } catch (UpdateProviderUnavailable) {
                $release = $this->githubProvider->release($version);
                $this->activeSource = 'github';
            }
            if ($release === null) return ['success' => false, 'message' => '未找到此版本'];
            if (empty($release['automatic_install_allowed'])) {
                return ['success' => false, 'message' => '当前 PHP 环境缺少 Sodium 扩展，可检查版本但不能自动安装签名更新包'];
            }
            $compatibility = $this->validateCompatibility((array)($release['compatibility'] ?? []));
            if ($compatibility !== null) return ['success' => false, 'message' => $compatibility];
            return $this->downloadUpdate(
                $version,
                (string)$release['download_url'],
                (string)$release['sha256'],
                (int)$release['size'],
                $this->activeSource
            );
        } catch (UpdateSecurityException $error) {
            return ['success' => false, 'message' => $error->getMessage()];
        } catch (\Throwable $error) {
            return ['success' => false, 'message' => '更新源暂时不可用：' . $error->getMessage()];
        }
    }
    
    /**
     * 安装更新
     * 
     * @param string $version 版本号
     * @return array 返回安装结果
     */
    public function installUpdate(string $version): array {
        $metadataFile = $this->downloadDir . '/update-' . $version . '.zip.json';
        if (is_file($metadataFile)) {
            $metadata = json_decode((string)file_get_contents($metadataFile), true);
            if (is_array($metadata) && in_array($metadata['source'] ?? '', ['shopagg', 'github'], true)) {
                $this->activeSource = $metadata['source'];
            }
        }
        $startedAt = microtime(true);
        $this->eventReporter->report('install_started', $this->getCurrentVersion(), $version, $this->activeSource);
        $result = $this->performInstallUpdate($version);
        $this->eventReporter->report(
            !empty($result['success']) ? 'install_succeeded' : 'install_failed',
            $this->getCurrentVersion(),
            $version,
            $this->activeSource,
            [
                'error_code' => !empty($result['success']) ? null : 'install_error',
                'duration_ms' => (int)round((microtime(true) - $startedAt) * 1000),
            ]
        );
        return $result;
    }

    private function performInstallUpdate(string $version): array {
        if (!$this->isValidVersion($version)) {
            return [
                'success' => false,
                'message' => '版本号格式无效',
            ];
        }

        // 文件路径完全由服务端版本号推导，不接受客户端提交的路径。
        $filepath = $this->downloadDir . '/update-' . $version . '.zip';
        $hashFile = $filepath . '.sha256';
        if (!$this->verifyPackageHash($filepath, $hashFile)) {
            return [
                'success' => false,
                'message' => '更新包不存在或完整性校验失败，请重新下载',
            ];
        }

        try {
            $extractDir = $this->downloadDir . '/extract-' . $version . '-' . bin2hex(random_bytes(8));
        } catch (\Throwable) {
            return ['success' => false, 'message' => '无法创建安全的更新临时目录'];
        }

        if (!mkdir($extractDir, 0700, true)) {
            return ['success' => false, 'message' => '无法创建更新临时目录'];
        }

        try {
            $extractResult = $this->extractArchiveSafely($filepath, $extractDir);
            if (!$extractResult['success']) {
                return $extractResult;
            }

            $extractedCodeDir = $this->findExtractedCodeDir($extractDir);
            if ($extractedCodeDir === null || !$this->isValidCodeDirectory($extractedCodeDir)) {
                return ['success' => false, 'message' => '更新包不包含有效的应用程序目录'];
            }

            // 校验通过后才创建备份并覆盖文件。
            $backupResult = $this->createBackup($version);
            if (!$backupResult['success']) {
                return [
                    'success' => false,
                    'message' => '创建备份失败：' . $backupResult['message'],
                ];
            }

            $copyResult = $this->copyDirectory(
                $extractedCodeDir,
                APP_ROOT,
                ['uploads', 'storage', '.env']
            );
            if (!$copyResult['success']) {
                return [
                    'success' => false,
                    'message' => '文件覆盖失败：' . $copyResult['message'],
                ];
            }

            $this->removeLegacyVersionFile();
            $migrationResult = $this->migrator->runAllPending();
            $this->logUpdate($version, 'success');

            $message = '更新成功！已安装版本 ' . $version;
            if ($migrationResult['success'] && !empty($migrationResult['executed'])) {
                $message .= '，执行了 ' . count($migrationResult['executed']) . ' 个数据库迁移';
            }

            return [
                'success' => true,
                'message' => $message,
                'backup_path' => $backupResult['backup_path'],
                'files_updated' => $copyResult['files_copied'] ?? 0,
                'migrations' => $migrationResult,
            ];
        } finally {
            $this->removeDirectory($extractDir);
        }
    }
    
    /**
     * 获取更新历史记录
     * 
     * @return array 返回本地更新日志
     */
    public function getUpdateHistory(): array {
        if (!is_file($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $history = [];
        
        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) >= 3) {
                $history[] = [
                    'timestamp' => $parts[0],
                    'version' => $parts[1],
                    'status' => $parts[2],
                    'message' => $parts[3] ?? '',
                ];
            }
        }
        
        // 按时间倒序
        return array_reverse($history);
    }
    
    /**
     * 获取备份列表
     * 
     * @return array 返回备份文件列表
     */
    public function getBackups(): array {
        $backups = [];
        if (is_dir($this->backupDir)) {
            $files = glob($this->backupDir . '/*.zip');
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'filepath' => $file,
                ];
            }
        }
        // 按时间倒序
        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return $backups;
    }
    
    /**
     * 删除备份文件
     * 
     * @param string $filename 备份文件名
     * @return bool 是否成功
     */
    public function deleteBackup(string $filename): bool {
        $filepath = $this->backupDir . '/' . basename($filename);
        if (is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }
    
    /**
     * 获取数据库迁移状态
     * 
     * @return array 迁移状态信息
     */
    public function getMigrationStatus(): array {
        return [
            'status' => $this->migrator->getStatus(),
            'pending' => $this->migrator->getPendingMigrations(),
            'executed' => $this->migrator->getExecutedMigrations(),
        ];
    }
    
    /**
     * 手动执行数据库迁移
     * 
     * @return array 执行结果
     */
    public function runMigrations(): array {
        return $this->migrator->runAllPending();
    }
    
    /**
     * 清理旧下载文件
     * 
     * @param int $keepDays 保留天数
     * @return int 清理的文件数量
     */
    public function cleanupDownloads(int $keepDays = 7): int {
        $count = 0;
        $cutoff = time() - ($keepDays * 86400);
        
        if (is_dir($this->downloadDir)) {
            $files = glob($this->downloadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff) {
                    unlink($file);
                    $count++;
                }
            }
        }
        
        return $count;
    }

    private function isValidVersion(string $version): bool {
        return preg_match('/^\d+\.\d+(?:\.\d+)?(?:[-+][0-9A-Za-z.-]+)?$/D', $version) === 1;
    }

    private function validateCompatibility(array $compatibility): ?string {
        $phpRequirement = (string)($compatibility['php'] ?? '');
        if (preg_match('/>=\s*(\d+(?:\.\d+){1,2})/', $phpRequirement, $matches) === 1
            && version_compare(PHP_VERSION, $matches[1], '<')) {
            return '此版本要求 PHP ' . $phpRequirement . '，当前为 ' . PHP_VERSION;
        }

        $sqliteRequirement = (string)($compatibility['sqlite'] ?? '');
        if (class_exists(\SQLite3::class) && preg_match('/>=\s*(\d+(?:\.\d+){1,2})/', $sqliteRequirement, $matches) === 1) {
            $sqliteVersion = (string)(\SQLite3::version()['versionString'] ?? '0.0.0');
            if (version_compare($sqliteVersion, $matches[1], '<')) {
                return '此版本要求 SQLite ' . $sqliteRequirement . '，当前为 ' . $sqliteVersion;
            }
        }

        $sourceRequirement = (string)($compatibility['source_version'] ?? '');
        if (preg_match('/>=\s*(\d+(?:\.\d+){1,2})/', $sourceRequirement, $matches) === 1
            && version_compare($this->getCurrentVersion(), $matches[1], '<')) {
            return '当前版本过旧，自动升级要求来源版本 ' . $sourceRequirement;
        }
        return null;
    }

    private function isAllowedDownloadUrl(string $url): bool {
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https') {
            return false;
        }

        $host = strtolower((string)($parts['host'] ?? ''));
        return in_array($host, self::ALLOWED_DOWNLOAD_HOSTS, true)
            && (int)($parts['port'] ?? 443) === 443
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }

    private function verifyPackageHash(string $filepath, string $hashFile): bool {
        if (!is_file($filepath) || !is_file($hashFile) || filesize($filepath) <= 0) {
            return false;
        }

        $expected = strtolower(trim((string)file_get_contents($hashFile)));
        if (preg_match('/^[a-f0-9]{64}$/D', $expected) !== 1) {
            return false;
        }

        $actual = hash_file('sha256', $filepath);
        return is_string($actual) && hash_equals($expected, strtolower($actual));
    }

    /**
     * 逐项解压并拒绝路径穿越、符号链接、超大文件和异常压缩比。
     */
    private function extractArchiveSafely(string $filepath, string $extractDir): array {
        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            return ['success' => false, 'message' => '无法打开更新包文件'];
        }

        try {
            if ($zip->numFiles <= 0 || $zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
                return ['success' => false, 'message' => '更新包文件数量异常'];
            }

            $totalSize = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                if (!is_array($stat)) {
                    return ['success' => false, 'message' => '无法读取更新包条目'];
                }

                $entryName = (string)($stat['name'] ?? '');
                $safeName = $this->normalizeArchivePath($entryName);
                if ($safeName === null) {
                    return ['success' => false, 'message' => '更新包包含非法路径'];
                }

                $isDirectory = str_ends_with($entryName, '/');
                if ($this->zipEntryIsSymlink($zip, $index)) {
                    return ['success' => false, 'message' => '更新包不得包含符号链接'];
                }

                $size = (int)($stat['size'] ?? 0);
                $compressedSize = (int)($stat['comp_size'] ?? 0);
                if ($size < 0 || $size > self::MAX_ARCHIVE_FILE_BYTES) {
                    return ['success' => false, 'message' => '更新包包含超大文件'];
                }

                $totalSize += $size;
                if ($totalSize > self::MAX_ARCHIVE_TOTAL_BYTES) {
                    return ['success' => false, 'message' => '更新包解压后体积过大'];
                }
                if ($size > 1048576 && ($compressedSize <= 0 || ($size / $compressedSize) > self::MAX_COMPRESSION_RATIO)) {
                    return ['success' => false, 'message' => '更新包压缩比异常'];
                }

                $destination = $extractDir . '/' . $safeName;
                if ($isDirectory) {
                    if (!is_dir($destination) && !mkdir($destination, 0700, true)) {
                        return ['success' => false, 'message' => '无法创建解压目录'];
                    }
                    continue;
                }

                $parent = dirname($destination);
                if (!is_dir($parent) && !mkdir($parent, 0700, true)) {
                    return ['success' => false, 'message' => '无法创建解压目录'];
                }

                $input = $zip->getStream($entryName);
                $output = fopen($destination, 'xb');
                if ($input === false || $output === false) {
                    if (is_resource($input)) {
                        fclose($input);
                    }
                    if (is_resource($output)) {
                        fclose($output);
                    }
                    return ['success' => false, 'message' => '无法安全解压更新包'];
                }

                $written = stream_copy_to_stream($input, $output, self::MAX_ARCHIVE_FILE_BYTES + 1);
                fclose($input);
                fclose($output);
                if ($written === false || $written !== $size || $written > self::MAX_ARCHIVE_FILE_BYTES) {
                    return ['success' => false, 'message' => '更新包条目大小校验失败'];
                }
            }
        } finally {
            $zip->close();
        }

        return ['success' => true, 'message' => '更新包解压完成'];
    }

    private function normalizeArchivePath(string $path): ?string {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\')) {
            return null;
        }

        $path = rtrim($path, '/');
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path) === 1) {
            return null;
        }

        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return null;
            }
        }

        return implode('/', $parts);
    }

    private function zipEntryIsSymlink(\ZipArchive $zip, int $index): bool {
        $operatingSystem = 0;
        $attributes = 0;
        if (!$zip->getExternalAttributesIndex($index, $operatingSystem, $attributes)) {
            return false;
        }

        return (($attributes >> 16) & 0xF000) === 0xA000;
    }

    private function isValidCodeDirectory(string $directory): bool {
        foreach (['index.php', 'app/routes.php', 'app/Core/Database.php'] as $required) {
            if (!is_file($directory . '/' . $required)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * 下载文件
     */
    private function downloadFile(string $url, string $filepath, bool $allowResume = true): array|false {
        if (!$this->isAllowedDownloadUrl($url)) {
            return false;
        }

        $temporaryPath = $filepath . '.part';
        $resumeSize = $allowResume && is_file($temporaryPath) ? (int)filesize($temporaryPath) : 0;
        if ($resumeSize < 0 || $resumeSize >= self::MAX_DOWNLOAD_BYTES) {
            @unlink($temporaryPath);
            $resumeSize = 0;
        }
        $ch = curl_init($url);
        $fp = fopen($temporaryPath, $resumeSize > 0 ? 'ab' : 'xb');
        if ($fp === false) {
            return false;
        }
        $hashContext = hash_init('sha256');
        if ($resumeSize > 0 && !hash_update_file($hashContext, $temporaryPath)) {
            fclose($fp);
            @unlink($temporaryPath);
            return false;
        }
        $contentRange = '';
        curl_setopt_array($ch, [
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use ($fp, $hashContext): int {
                hash_update($hashContext, $chunk);
                $written = fwrite($fp, $chunk);
                return $written === false ? 0 : $written;
            },
            CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$contentRange): int {
                if (stripos($header, 'Content-Range:') === 0) $contentRange = trim(substr($header, strlen('Content-Range:')));
                return strlen($header);
            },
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_USERAGENT => 'shopagg-b2b-updater/1.0',
            CURLOPT_NOPROGRESS => false,
            CURLOPT_XFERINFOFUNCTION => static function ($handle, float $downloadTotal, float $downloaded) use ($resumeSize): int {
                return ($resumeSize + $downloaded) > self::MAX_DOWNLOAD_BYTES ? 1 : 0;
            },
        ]);
        if ($resumeSize > 0) curl_setopt($ch, CURLOPT_RESUME_FROM_LARGE, $resumeSize);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        fclose($fp);
        
        if ($resumeSize > 0 && $httpCode === 200) {
            @unlink($temporaryPath);
            return $this->downloadFile($url, $filepath, false);
        }

        if (!$result || !in_array($httpCode, [200, 206], true) || !$this->isAllowedDownloadUrl($effectiveUrl)
            || ($resumeSize > 0 && ($httpCode !== 206 || !str_starts_with($contentRange, 'bytes ' . $resumeSize . '-')))
            || !is_file($temporaryPath) || filesize($temporaryPath) <= 0
            || filesize($temporaryPath) > self::MAX_DOWNLOAD_BYTES) {
            if ($httpCode > 0 && !in_array($httpCode, [200, 206], true)) @unlink($temporaryPath);
            return false;
        }

        $hash = hash_final($hashContext);
        $size = (int)filesize($temporaryPath);
        if (!rename($temporaryPath, $filepath)) return false;
        return ['sha256' => $hash, 'size' => $size];
    }
    
    /**
     * 创建备份
     */
    private function createBackup(string $version): array {
        $backupFile = $this->backupDir . '/backup-' . date('Ymd-His') . '-v' . $version . '.zip';
        
        $zip = new \ZipArchive();
        if ($zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return [
                'success' => false,
                'message' => '无法创建备份文件',
            ];
        }
        
        // 要备份的目录
        $backupDirs = ['app', 'themes', 'index.php', '.htaccess'];
        foreach ($backupDirs as $item) {
            $path = APP_ROOT . '/' . $item;
            if (is_dir($path)) {
                $this->addDirToZip($zip, $path, $item);
            } elseif (is_file($path)) {
                $zip->addFile($path, $item);
            }
        }
        
        $zip->close();
        
        return [
            'success' => true,
            'backup_path' => $backupFile,
        ];
    }
    
    /**
     * 添加目录到 ZIP
     */
    private function addDirToZip(\ZipArchive $zip, string $dir, string $basePath): void {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = $basePath . '/' . substr($filePath, strlen($dir) + 1);
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }
    
    /**
     * 查找解压后的代码目录
     */
    private function findExtractedCodeDir(string $extractDir): ?string {
        // GitHub source zip 解压后会包含一个目录如：shopagg-b2b-website-1.0.0
        $entries = glob($extractDir . '/*', GLOB_ONLYDIR);
        if (count($entries) === 1) {
            return $entries[0];
        }
        
        // 如果没有找到单个子目录，返回原目录
        return $extractDir;
    }
    
    /**
     * 复制目录（覆盖式）
     */
    private function copyDirectory(string $source, string $dest, array $exclude = []): array {
        $filesCopied = 0;
        
        if (!is_dir($source)) {
            return [
                'success' => false,
                'message' => '源目录不存在',
                'files_copied' => 0,
            ];
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($source . '/', '', $item->getPathname());

            if ($this->normalizeArchivePath($relativePath) === null || $item->isLink()) {
                return [
                    'success' => false,
                    'message' => '源目录包含不安全的文件路径',
                    'files_copied' => $filesCopied,
                ];
            }
            
            // 检查是否在排除列表中
            $excluded = false;
            foreach ($exclude as $ex) {
                if ($relativePath === $ex || str_starts_with($relativePath, $ex . '/')) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) {
                continue;
            }
            
            $destPath = $dest . '/' . $relativePath;
            if (!$this->isSafeDestinationPath($dest, $relativePath)) {
                return [
                    'success' => false,
                    'message' => '目标路径包含符号链接：' . $relativePath,
                    'files_copied' => $filesCopied,
                ];
            }
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    if (!mkdir($destPath, 0755, true)) {
                        return [
                            'success' => false,
                            'message' => '无法创建目标目录：' . $relativePath,
                            'files_copied' => $filesCopied,
                        ];
                    }
                }
            } else {
                if (!copy($item->getPathname(), $destPath)) {
                    return [
                        'success' => false,
                        'message' => '无法覆盖文件：' . $relativePath,
                        'files_copied' => $filesCopied,
                    ];
                }
                $filesCopied++;
            }
        }
        
        return [
            'success' => true,
            'message' => '复制完成',
            'files_copied' => $filesCopied,
        ];
    }

    private function isSafeDestinationPath(string $base, string $relativePath): bool {
        $current = rtrim($base, '/');
        foreach (explode('/', $relativePath) as $part) {
            $current .= '/' . $part;
            if (is_link($current)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * 删除目录
     */
    private function removeDirectory(string $dir): bool {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
    
    /**
     * 清理旧版本文件
     */
    private function removeLegacyVersionFile(): void {
        $versionFile = APP_ROOT . '/version.php';
        if (is_file($versionFile)) {
            unlink($versionFile);
        }
    }
    
    /**
     * 记录更新日志
     */
    private function logUpdate(string $version, string $status, string $message = ''): void {
        $line = date('Y-m-d H:i:s') . '|' . $version . '|' . $status . '|' . $message . "\n";
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 格式化字节数
     */
    private function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
