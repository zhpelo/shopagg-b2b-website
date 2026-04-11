<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Migrator;

/**
 * 程序更新管理器
 * 
 * 处理从 GitHub 获取最新版本、下载更新包、执行更新等操作
 */
class Updater {
    
    /** GitHub API 基础地址 */
    private const GITHUB_API_BASE = 'https://api.github.com/repos/zhpelo/shopagg-b2b-website';
    
    /** GitHub 仓库页面 */
    private const GITHUB_REPO_URL = 'https://github.com/zhpelo/shopagg-b2b-website';
    
    /** 当前版本号（从配置文件或代码中获取） */
    private const CURRENT_VERSION = '1.0.0';
    
    /** 更新包下载目录 */
    private string $downloadDir;
    
    /** 更新备份目录 */
    private string $backupDir;
    
    /** 更新日志文件 */
    private string $logFile;
    
    /** 迁移管理器 */
    private Migrator $migrator;
    
    public function __construct() {
        $this->downloadDir = APP_ROOT . '/storage/updates';
        $this->backupDir = APP_ROOT . '/storage/backups';
        $this->logFile = APP_ROOT . '/storage/update.log';
        $this->migrator = new Migrator();
        
        // 确保目录存在
        if (!is_dir($this->downloadDir)) {
            mkdir($this->downloadDir, 0755, true);
        }
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * 从 name 和 tag_name 中提取版本号
     * 优先使用 name（如 v1.0.0），如果不符合版本格式则使用 tag_name
     */
    private function extractVersion(string $name, string $tagName): string {
        // 尝试从 name 中提取版本号（如 "v1.0.0" -> "1.0.0"）
        if (preg_match('/v?(\d+\.\d+(?:\.\d+)?)/', $name, $matches)) {
            return $matches[1];
        }
        // 尝试从 tag_name 中提取版本号
        if (preg_match('/v?(\d+\.\d+(?:\.\d+)?)/', $tagName, $matches)) {
            return $matches[1];
        }
        // 如果都不匹配，返回 tag_name（去掉 v 前缀）
        return ltrim($tagName, 'v') ?: '0.0.0';
    }
    
    /**
     * 获取当前版本号
     */
    public function getCurrentVersion(): string {
        // 尝试从 version.php 文件读取版本号
        $versionFile = APP_ROOT . '/version.php';
        if (is_file($versionFile)) {
            $version = require $versionFile;
            if (is_string($version)) {
                return $version;
            }
        }
        return self::CURRENT_VERSION;
    }
    
    /**
     * 获取 GitHub 最新 Release 信息
     * 
     * @return array|null 返回最新版本信息，失败返回 null
     */
    public function getLatestRelease(): ?array {
        $url = self::GITHUB_API_BASE . '/releases/latest';
        $response = $this->httpGet($url);
        
        if ($response === null) {
            return null;
        }
        
        $data = json_decode($response, true);
        if (!$data || !isset($data['tag_name'])) {
            return null;
        }
        
        // 尝试从 name 或 tag_name 中提取版本号
        $version = $this->extractVersion($data['name'] ?? '', $data['tag_name'] ?? '');
        
        return [
            'version' => $version,
            'name' => $data['name'] ?? $data['tag_name'] ?? 'Unknown',
            'body' => $data['body'] ?? '',
            'published_at' => $data['published_at'] ?? '',
            'html_url' => $data['html_url'] ?? '',
            'assets' => $data['assets'] ?? [],
            'is_prerelease' => $data['prerelease'] ?? false,
            'is_draft' => $data['draft'] ?? false,
        ];
    }
    
    /**
     * 获取所有 Release 列表（用于查看更新历史）
     * 
     * @param int $page 页码
     * @param int $perPage 每页数量
     * @return array 返回版本列表
     */
    public function getReleases(int $page = 1, int $perPage = 10): array {
        $url = self::GITHUB_API_BASE . '/releases?page=' . $page . '&per_page=' . $perPage;
        $response = $this->httpGet($url);
        
        if ($response === null) {
            return [];
        }
        
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return [];
        }
        
        $releases = [];
        foreach ($data as $release) {
            $version = $this->extractVersion($release['name'] ?? '', $release['tag_name'] ?? '');
            $releases[] = [
                'version' => $version,
                'name' => $release['name'] ?? $release['tag_name'] ?? 'Unknown',
                'body' => $release['body'] ?? '',
                'published_at' => $release['published_at'] ?? '',
                'html_url' => $release['html_url'] ?? '',
                'author' => $release['author']['login'] ?? 'unknown',
                'is_prerelease' => $release['prerelease'] ?? false,
                'is_draft' => $release['draft'] ?? false,
                'assets_count' => count($release['assets'] ?? []),
            ];
        }
        
        return $releases;
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
                'message' => '无法获取最新版本信息，请检查网络连接或稍后重试',
                'current_version' => $currentVersion,
                'latest_version' => null,
                'has_update' => false,
            ];
        }
        
        $latestVersion = $latestRelease['version'];
        $hasUpdate = version_compare($latestVersion, $currentVersion, '>');
        
        return [
            'success' => true,
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'has_update' => $hasUpdate,
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
    public function downloadUpdate(string $version, string $downloadUrl): array {
        $filename = 'update-' . $version . '.zip';
        $filepath = $this->downloadDir . '/' . $filename;
        
        // 如果文件已存在，直接返回
        if (is_file($filepath) && filesize($filepath) > 0) {
            return [
                'success' => true,
                'message' => '更新包已存在',
                'filepath' => $filepath,
                'filename' => $filename,
            ];
        }
        
        // 下载文件
        $result = $this->downloadFile($downloadUrl, $filepath);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => '下载更新包失败',
                'filepath' => null,
                'filename' => null,
            ];
        }
        
        return [
            'success' => true,
            'message' => '下载成功',
            'filepath' => $filepath,
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
        // 先获取 release 信息以获取正确的 tag_name
        $latest = $this->getLatestRelease();
        if ($latest !== null && $latest['version'] === $version) {
            // 使用 API 返回的 zipball_url 或通过 tag_name 构建 URL
            $tagName = $this->getTagNameByVersion($version);
            $url = 'https://github.com/zhpelo/shopagg-b2b-website/archive/refs/tags/' . $tagName . '.zip';
        } else {
            // 默认尝试 v 前缀
            $url = 'https://github.com/zhpelo/shopagg-b2b-website/archive/refs/tags/v' . $version . '.zip';
        }
        return $this->downloadUpdate($version, $url);
    }
    
    /**
     * 根据版本号获取对应的 tag_name
     */
    private function getTagNameByVersion(string $version): string {
        $url = self::GITHUB_API_BASE . '/releases';
        $response = $this->httpGet($url);
        
        if ($response === null) {
            return 'v' . $version;
        }
        
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return 'v' . $version;
        }
        
        foreach ($data as $release) {
            $releaseVersion = $this->extractVersion($release['name'] ?? '', $release['tag_name'] ?? '');
            if ($releaseVersion === $version) {
                return $release['tag_name'];
            }
        }
        
        return 'v' . $version;
    }
    
    /**
     * 安装更新
     * 
     * @param string $version 版本号
     * @param string $filepath 更新包路径
     * @return array 返回安装结果
     */
    public function installUpdate(string $version, string $filepath): array {
        if (!is_file($filepath)) {
            return [
                'success' => false,
                'message' => '更新包文件不存在',
            ];
        }
        
        // 创建备份
        $backupResult = $this->createBackup($version);
        if (!$backupResult['success']) {
            return [
                'success' => false,
                'message' => '创建备份失败：' . $backupResult['message'],
            ];
        }
        
        // 解压目录
        $extractDir = $this->downloadDir . '/extract-' . $version;
        if (!is_dir($extractDir)) {
            mkdir($extractDir, 0755, true);
        }
        
        // 解压更新包
        $zip = new \ZipArchive();
        if ($zip->open($filepath) !== true) {
            return [
                'success' => false,
                'message' => '无法打开更新包文件',
            ];
        }
        
        $zip->extractTo($extractDir);
        $zip->close();
        
        // 查找解压后的实际代码目录（GitHub source zip 通常包含一个顶层目录）
        $extractedCodeDir = $this->findExtractedCodeDir($extractDir);
        if ($extractedCodeDir === null) {
            // 清理临时文件
            $this->removeDirectory($extractDir);
            return [
                'success' => false,
                'message' => '无法找到有效的代码目录',
            ];
        }
        
        // 执行文件覆盖
        $excludeFiles = [
            'uploads',
            'storage',
            '.env',
            'version.php',
        ];
        
        $copyResult = $this->copyDirectory($extractedCodeDir, APP_ROOT, $excludeFiles);
        
        // 清理临时文件
        $this->removeDirectory($extractDir);
        
        if (!$copyResult['success']) {
            return [
                'success' => false,
                'message' => '文件覆盖失败：' . $copyResult['message'],
            ];
        }
        
        // 更新版本号
        $this->updateVersionFile($version);
        
        // 执行数据库迁移（新版本可能包含新的迁移文件）
        $migrationResult = $this->migrator->runAllPending();
        
        // 记录更新日志
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
    
    /**
     * HTTP GET 请求
     */
    private function httpGet(string $url): ?string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'shopagg-b2b-updater/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'X-GitHub-Api-Version: 2022-11-28',
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return null;
        }
        
        return $response;
    }
    
    /**
     * 下载文件
     */
    private function downloadFile(string $url, string $filepath): bool {
        $ch = curl_init($url);
        $fp = fopen($filepath, 'w+');
        
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_USERAGENT => 'shopagg-b2b-updater/1.0',
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        
        if (!$result || $httpCode !== 200) {
            if (is_file($filepath)) {
                unlink($filepath);
            }
            return false;
        }
        
        return true;
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
            
            // 检查是否在排除列表中
            $excluded = false;
            foreach ($exclude as $ex) {
                if (str_starts_with($relativePath, $ex)) {
                    $excluded = true;
                    break;
                }
            }
            if ($excluded) {
                continue;
            }
            
            $destPath = $dest . '/' . $relativePath;
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
                $filesCopied++;
            }
        }
        
        return [
            'success' => true,
            'message' => '复制完成',
            'files_copied' => $filesCopied,
        ];
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
     * 更新版本文件
     */
    private function updateVersionFile(string $version): void {
        $versionFile = APP_ROOT . '/version.php';
        $content = "<?php\n// 当前系统版本\nreturn '" . $version . "';\n";
        file_put_contents($versionFile, $content);
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
