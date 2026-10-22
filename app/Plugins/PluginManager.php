<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Core\Database;
use App\Plugins\Contracts\PluginInterface;
use SQLite3;

final class PluginManager {
    private const MAX_ENTRIES = 3000;
    private const MAX_TOTAL_BYTES = 268435456;
    private const MAX_FILE_BYTES = 52428800;
    private const ALLOWED_ASSET_EXTENSIONS = ['css','js','mjs','png','jpg','jpeg','gif','webp','svg','ico','woff','woff2','ttf','otf','map','json'];
    private const RESERVED_PREFIXES = ['/admin', '/uploads/', '/assets/', '/plugin-cron'];
    private const RESERVED_ROUTES = ['/', '/products', '/product/{}', '/product-category/{}', '/blog', '/blog/{}', '/cases', '/case/{}', '/page/{}', '/about', '/contact', '/inquiry', '/robots.txt', '/sitemap.xml'];

    private SQLite3 $db;
    private PluginRegistry $registry;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->db->enableExceptions(true);
        $this->registry = new PluginRegistry($this->db);
    }

    public function all(): array { return $this->registry->all(); }

    /** Discover dropped-in packages without loading PHP, running migrations or enabling them. */
    public function discoverLocal(): array {
        $root = APP_ROOT . '/storage/plugins';
        $report = ['registered' => [], 'issues' => []];
        if (!is_dir($root)) return $report;
        if (is_link($root)) {
            $report['issues'][] = ['directory' => 'storage/plugins', 'message' => '插件根目录不能是符号链接'];
            return $report;
        }
        // Serialize with ZIP installation/removal and concurrent visits to the list.
        return PluginOperationLock::exclusive(function () use ($root, $report): array {
            $entries = scandir($root);
            if ($entries === false) {
                $report['issues'][] = ['directory' => 'storage/plugins', 'message' => '无法读取插件目录'];
                return $report;
            }
            foreach ($entries as $name) {
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $name) || strlen($name) > 64) continue;
                $directory = $root . '/' . $name;
                if (!is_dir($directory) && !is_link($directory)) continue;
                $row = $this->registry->find($name);
                // Directory discovery is not an implicit update of installed/active code.
                if ($row !== null && $row['status'] !== 'removed') continue;
                if (!file_exists($directory . '/plugin.json') && !is_link($directory)) continue;
                try {
                    if (is_link($directory)) throw new \RuntimeException('插件目录不能是符号链接');
                    $count = 0;
                    $bytes = 0;
                    foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $file) {
                        if ($file->isLink()) throw new \RuntimeException('插件包不能包含符号链接');
                        if (++$count > self::MAX_ENTRIES) throw new \RuntimeException('插件包文件数量超出限制');
                        if ($file->isFile()) {
                            $size = $file->getSize();
                            $bytes += $size;
                            if ($size > self::MAX_FILE_BYTES || $bytes > self::MAX_TOTAL_BYTES) throw new \RuntimeException('插件包大小超出限制');
                        } elseif (!$file->isDir()) throw new \RuntimeException('插件包包含特殊文件');
                    }
                    if (filesize($directory . '/plugin.json') > 1048576) throw new \RuntimeException('plugin.json 超过 1MB');
                    $validation = $this->validateDirectory($directory, false);
                    if (!$validation['valid']) throw new \RuntimeException(implode('；', array_column($validation['errors'], 'message')));
                    $manifest = $validation['manifest'];
                    if ($manifest['id'] !== $name) throw new \RuntimeException('目录名必须与 plugin.json 的 id 一致');
                    $this->db->exec('BEGIN IMMEDIATE');
                    try {
                        $this->registry->save($manifest, 'local', 'installed');
                        $this->registry->setStatus($name, 'installed');
                        $this->registry->log($name, 'info', '从本地插件目录自动发现，尚未启用');
                        $this->db->exec('COMMIT');
                    } catch (\Throwable $e) {
                        $this->db->exec('ROLLBACK');
                        throw $e;
                    }
                    $report['registered'][] = $name;
                    foreach ($validation['warnings'] ?? [] as $warning) {
                        if ($warning['code'] === 'environment.unsatisfied') $report['issues'][] = ['directory' => $name, 'message' => $warning['message'] . '（已登记，满足环境要求后才能启用）'];
                    }
                } catch (\Throwable $e) {
                    $report['issues'][] = ['directory' => $name, 'message' => $e->getMessage()];
                }
            }
            return $report;
        });
    }

    public function installZip(string $zipPath, string $source = 'private'): array {
        if (!class_exists(\ZipArchive::class)) throw new \RuntimeException('当前环境未启用 ZipArchive');
        $workspace = $this->tempDirectory('install-');
        try {
            $this->extractZip($zipPath, $workspace);
            $package = $this->locatePackage($workspace);
            return $this->installDirectory($package, $source);
        } finally {
            $this->removeDirectory($workspace);
        }
    }

    public function validateDirectory(string $directory, ?bool $loadContracts = null): array {
        $validation = PluginManifest::validateFile(rtrim($directory, '/') . '/plugin.json');
        if (!$validation['valid']) return $validation;
        $manifest = $validation['manifest'];
        $loadContracts ??= !$this->hasLoadedClasses($manifest);
        $errors = [];
        $warnings = [];
        foreach (PluginManifest::environmentErrors($manifest) as $message) {
            $warnings[] = ['code' => 'environment.unsatisfied', 'path' => 'requires', 'message' => $message, 'suggestion' => '可先安装；满足服务器环境要求后再启用'];
        }
        if (!is_file(rtrim($directory, '/') . '/README.md')) $errors[] = ['code' => 'package.readme', 'path' => 'README.md', 'message' => '缺少 README.md', 'suggestion' => '添加插件用途、配置和卸载说明'];
        foreach ($manifest['autoload']['psr4'] as $path) {
            if (!is_dir(rtrim($directory, '/') . '/' . trim((string)$path, '/'))) {
                $errors[] = ['code' => 'autoload.directory', 'path' => 'autoload.psr4', 'message' => "自动加载目录不存在：{$path}"];
            }
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') continue;
            try {
                token_get_all((string)file_get_contents($file->getPathname()), TOKEN_PARSE);
            } catch (\ParseError $e) {
                $errors[] = ['code' => 'php.syntax', 'path' => substr($file->getPathname(), strlen(rtrim($directory, '/')) + 1), 'line' => $e->getLine(), 'message' => $e->getMessage(), 'suggestion' => '修复 PHP 语法后重新验证'];
            }
        }
        if ($errors === [] && $warnings === [] && $loadContracts) {
            try {
                $errors = array_merge($errors, $this->contractErrors($directory, $manifest));
            } catch (\Throwable $e) {
                $errors[] = ['code' => 'contract.load', 'path' => 'autoload.psr4', 'message' => $e->getMessage(), 'suggestion' => '检查类加载期间使用的父类、接口和 PHP 扩展'];
            }
        } elseif ($errors === [] && !$loadContracts) {
            $warnings[] = ['code' => 'contract.deferred', 'path' => 'autoload.psr4', 'message' => '当前请求已加载此插件的类；已校验新包语法，类契约检查需在独立 CLI 请求中执行', 'suggestion' => '安装前使用 plugin:test 校验新包'];
        } elseif ($errors === [] && $warnings !== []) {
            $warnings[] = ['code' => 'contract.skipped', 'path' => 'requires', 'message' => '当前服务器环境不满足要求，已跳过类加载契约测试', 'suggestion' => '在满足插件环境要求的服务器上再次执行 plugin:test'];
        }
        return ['valid' => $errors === [], 'errors' => $errors, 'warnings' => $warnings, 'manifest' => $manifest];
    }

    public function installDirectory(string $directory, string $source = 'private'): array {
        return PluginOperationLock::exclusive(fn(): array => $this->replaceDirectory($directory, $source));
    }

    private function replaceDirectory(string $directory, string $source): array {
        $workspace = PluginFiles::workspace();
        $destination = null;
        $assets = null;
        $codeReplaced = false;
        $assetsReplaced = false;
        $restore = false;
        $cleanup = true;
        try {
            // Validate the exact staged package that will be installed.
            PluginFiles::copy($directory, $workspace . '/package');
            $basic = PluginManifest::validateFile($workspace . '/package/plugin.json');
            if (!$basic['valid']) return $basic;
            $validation = $this->validateDirectory($workspace . '/package', !$this->hasLoadedClasses($basic['manifest']));
            if (!$validation['valid']) return $validation;
            $manifest = $validation['manifest'];
            $id = $manifest['id'];
            $version = $manifest['version'];
            $row = $this->registry->find($id);
            $enabled = ($row['status'] ?? '') === 'enabled';
            $destination = PluginFiles::code($id);
            $assets = PluginFiles::assets($id);
            if (is_link($destination) || is_link($assets)) throw new \RuntimeException('插件安装目录不允许符号链接');
            if (is_dir($destination) && $row === null) throw new \RuntimeException('插件目录已存在但未注册，不能覆盖');
            if ($enabled) {
                $this->assertCanEnable($id, $manifest);
                $this->assertCompatibleDependents($id, $manifest);
            }
            $this->backupDatabase($workspace . '/database.sqlite');
            $restore = true;
            PluginFiles::invalidate($destination);
            if (is_dir($destination)) PluginFiles::move($destination, $workspace . '/previous-code');
            $codeReplaced = true;
            PluginFiles::move($workspace . '/package', $destination);
            if (is_dir($assets)) PluginFiles::move($assets, $workspace . '/previous-assets');
            $assetsReplaced = true;
            $this->registry->save($manifest, $source, 'installed');
            $status = $enabled ? 'enabled' : (($row['status'] ?? '') === 'disabled' ? 'disabled' : 'installed');
            $this->registry->setStatus($id, $status, $enabled ? $version : null);
            if ($enabled) {
                foreach ($this->registry->enabled() as $dependent) {
                    if ($dependent['plugin_id'] !== $id) $this->assertRequiredServices((string)$dependent['plugin_id'], $dependent['manifest']);
                }
            }
            $this->rebuildCache();
            if ($enabled) {
                // Updates preserve activation, like WordPress: do not run activate again.
                $this->registerAutoload($manifest, $destination);
                $this->runMigrations($id, $version, $manifest, $destination);
                $this->seedScheduledJobs($this->standaloneContext($id, $version, $destination), $manifest);
                $this->publishAssets($id, $destination);
            }
            PluginFiles::invalidate($destination);
            $this->registry->log($id, 'info', ($row === null || $row['status'] === 'removed' ? '已安装版本 ' : '已更新至版本 ') . $version);
            $this->rebuildCache();
            $restore = false;
            return ['valid' => true, 'errors' => [], 'warnings' => $validation['warnings'] ?? [], 'manifest' => $manifest, 'installed_path' => $destination, 'updated' => $row !== null && $row['status'] !== 'removed'];
        } catch (\Throwable $e) {
            if ($restore) {
                try {
                    if ($codeReplaced) $this->restoreDirectory($destination, $workspace . '/previous-code');
                    if ($assetsReplaced) $this->restoreDirectory($assets, $workspace . '/previous-assets');
                    $this->restoreDatabase($workspace . '/database.sqlite');
                    $this->rebuildCache();
                } catch (\Throwable $recovery) {
                    $cleanup = false;
                    throw new \RuntimeException('插件更新失败且恢复未完成，恢复文件保留于 ' . $workspace . '：' . $recovery->getMessage(), 0, $e);
                }
            }
            throw $e;
        } finally {
            if ($cleanup) $this->cleanupWorkspace($workspace);
        }
    }

    public function enable(string $pluginId): void {
        PluginOperationLock::exclusive(fn() => $this->enablePlugin($pluginId));
    }

    private function enablePlugin(string $pluginId): void {
        $row = $this->requirePlugin($pluginId);
        if ($row['status'] === 'enabled') return;
        $manifest = $row['manifest'];
        $version = (string)$row['installed_version'];
        $this->assertCanEnable($pluginId, $manifest);
        $base = PluginFiles::code($pluginId);
        if (!is_dir($base)) throw new \RuntimeException('插件代码目录不存在');
        $this->registerAutoload($manifest, $base);
        $workspace = PluginFiles::workspace();
        $assets = PluginFiles::assets($pluginId);
        $assetsReplaced = false;
        $backup = false;
        $cleanup = true;
        try {
            $this->backupDatabase($workspace . '/database.sqlite');
            $backup = true;
            if (is_dir($assets)) PluginFiles::move($assets, $workspace . '/previous-assets');
            $assetsReplaced = true;
            $this->runMigrations($pluginId, $version, $manifest, $base);
            $context = $this->standaloneContext($pluginId, $version, $base);
            $this->invokeLifecycle($manifest, 'activate', $context);
            $this->seedScheduledJobs($context, $manifest);
            $this->publishAssets($pluginId, $base);
            $this->registry->setStatus($pluginId, 'enabled', $version);
            $this->registry->log($pluginId, 'info', "已启用版本 {$version}");
            $this->rebuildCache();
        } catch (\Throwable $e) {
            try {
                if ($assetsReplaced) $this->restoreDirectory($assets, $workspace . '/previous-assets');
                if ($backup) $this->restoreDatabase($workspace . '/database.sqlite');
                $this->rebuildCache();
            } catch (\Throwable $recovery) {
                $cleanup = false;
                throw new \RuntimeException('插件启用失败且恢复未完成，恢复文件保留于 ' . $workspace . '：' . $recovery->getMessage(), 0, $e);
            }
            throw new \RuntimeException('插件启用失败，已恢复数据库和静态资源：' . $e->getMessage(), 0, $e);
        } finally {
            if ($cleanup) $this->cleanupWorkspace($workspace);
        }
    }

    public function disable(string $pluginId): void {
        PluginOperationLock::exclusive(fn() => $this->disablePlugin($pluginId));
    }

    private function disablePlugin(string $pluginId): void {
        $row = $this->requirePlugin($pluginId);
        if ($row['status'] === 'enabled') {
            $version = (string)$row['active_version'];
            $base = PluginFiles::code($pluginId);
            $activeManifest = is_array($row['active_manifest'] ?? null) ? $row['active_manifest'] : $row['manifest'];
            $this->registerAutoload($activeManifest, $base);
            $this->invokeLifecycle($activeManifest, 'deactivate', $this->standaloneContext($pluginId, $version, $base));
        }
        $this->registry->setStatus($pluginId, 'disabled', null);
        $this->registry->log($pluginId, 'info', '已停用');
        $this->rebuildCache();
    }

    public function remove(string $pluginId, bool $purgeData = false): void {
        PluginOperationLock::exclusive(fn() => $this->removePlugin($pluginId, $purgeData));
    }

    private function removePlugin(string $pluginId, bool $purgeData): void {
        $row = $this->requirePlugin($pluginId);
        if ($row['status'] === 'enabled') $this->disable($pluginId);
        $version = (string)($row['active_version'] ?: $row['installed_version']);
        $base = PluginFiles::code($pluginId);
        if (is_dir($base)) {
            $lifecycleManifest = $row['active_version'] && is_array($row['active_manifest'] ?? null) ? $row['active_manifest'] : $row['manifest'];
            $this->registerAutoload($lifecycleManifest, $base);
            $this->invokeLifecycle($lifecycleManifest, 'uninstall', $this->standaloneContext($pluginId, $version, $base));
        }
        PluginFiles::invalidate($base);
        $this->removeDirectory($base);
        $this->removeDirectory(PluginFiles::assets($pluginId));
        $this->removeDirectory(APP_ROOT . '/uploads/plugins/' . $pluginId);
        if ($purgeData) {
            $prefix = 'p_' . str_replace('-', '_', $pluginId) . '_';
            $stmt = $this->db->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE :prefix");
            $stmt->bindValue(':prefix', $prefix . '%', SQLITE3_TEXT);
            $result = $stmt->execute();
            $tables = [];
            while ($result && ($table = $result->fetchArray(SQLITE3_ASSOC))) $tables[] = (string)$table['name'];
            if ($result) $result->finalize();
            foreach ($tables as $table) $this->db->exec('DROP TABLE IF EXISTS "' . str_replace('"', '""', $table) . '"');
            $this->registry->delete($pluginId);
        } else {
            $this->registry->setStatus($pluginId, 'removed', null);
            $this->registry->log($pluginId, 'info', '插件代码已移除，数据已保留');
        }
        $this->rebuildCache();
    }

    public function saveSettings(string $pluginId, array $values): void {
        $row = $this->requirePlugin($pluginId);
        $result = PluginRuntime::instance()->filters()->apply('admin.plugin.settings_save', ['handled' => false, 'error' => null], ['plugin_id' => $pluginId, 'values' => $values]);
        if (!empty($result['error'])) throw new \RuntimeException((string)$result['error']);
        if ($result['handled'] === true) return;
        $schema = $row['manifest']['settings'] ?? [];
        foreach ($schema as $key => $field) {
            if (!array_key_exists($key, $values)) continue;
            $value = $values[$key];
            $type = (string)($field['type'] ?? 'text');
            if ($type === 'checkbox') $value = in_array($value, ['1', 1, true, 'true'], true);
            elseif ($type === 'number') $value = is_numeric($value) ? (float)$value : 0;
            elseif (is_array($value)) $value = array_values($value);
            else $value = trim((string)$value);
            $this->registry->setOption($pluginId, (string)$key, $value, (bool)($field['autoload'] ?? false));
        }
    }

    public function rebuildCache(): array {
        $cache = (new PluginCache())->rebuild($this->registry->enabled());
        PluginRuntime::reset();
        return $cache;
    }

    public function diagnostics(): array {
        return [
            'php' => PHP_VERSION,
            'shopagg' => defined('APP_VERSION') ? APP_VERSION : 'unknown',
            'sqlite' => SQLite3::version()['versionString'] ?? 'unknown',
            'zip' => class_exists(\ZipArchive::class),
            'curl' => function_exists('curl_init'),
            'storage_writable' => is_writable(APP_ROOT . '/storage'),
            'uploads_writable' => is_writable(APP_ROOT . '/uploads'),
            'plugin_assets_writable' => is_writable(APP_ROOT . '/assets')
        ];
    }

    private function requirePlugin(string $pluginId): array {
        $row = $this->registry->find($pluginId);
        if ($row === null) throw new \RuntimeException("插件不存在：{$pluginId}");
        return $row;
    }

    private function hasLoadedClasses(array $manifest): bool {
        foreach (get_declared_classes() as $class) {
            foreach (array_keys($manifest['autoload']['psr4']) as $prefix) {
                if (str_starts_with($class, $prefix)) return true;
            }
        }
        return false;
    }

    private function assertCanEnable(string $id, array $manifest): void {
        $environment = PluginManifest::environmentErrors($manifest);
        if ($environment !== []) throw new \RuntimeException(implode('；', $environment));
        $this->assertDependencies($id, $manifest);
        $this->assertRequiredServices($id, $manifest);
        $this->assertNoRouteConflicts($id, $manifest);
        $this->assertNoServiceConflicts($id, $manifest);
    }

    private function assertCompatibleDependents(string $id, array $manifest): void {
        foreach ($this->registry->enabled() as $row) {
            if ($row['plugin_id'] === $id) continue;
            foreach (($row['manifest']['dependencies'] ?? []) as $dependency) {
                $dependencyId = is_array($dependency) ? (string)($dependency['id'] ?? '') : (string)$dependency;
                $constraint = is_array($dependency) ? (string)($dependency['version'] ?? '*') : '*';
                if ($dependencyId === $id && !PluginManifest::satisfies($manifest['version'], $constraint)) throw new \RuntimeException('新版本不满足已启用插件的依赖：' . $row['plugin_id']);
            }
        }
    }

    private function restoreDirectory(string $destination, string $backup): void {
        PluginFiles::invalidate($destination);
        PluginFiles::remove($destination);
        if (is_dir($backup)) PluginFiles::move($backup, $destination);
        PluginFiles::invalidate($destination);
    }

    private function restoreDatabase(string $backup): void {
        $source = new SQLite3($backup, SQLITE3_OPEN_READONLY);
        try { if (!$source->backup($this->db)) throw new \RuntimeException('无法恢复插件数据库快照'); }
        finally { $source->close(); }
    }

    private function cleanupWorkspace(string $workspace): void {
        try { PluginFiles::remove($workspace); }
        catch (\Throwable $e) { error_log('[Plugin Cleanup] ' . $e->getMessage()); }
    }

    private function contractErrors(string $directory, array $manifest): array {
        $errors = [];
        $serviceContracts = [
            'member.identity' => \App\Plugins\Contracts\MemberIdentityProviderInterface::class,
            'order.manager' => \App\Plugins\Contracts\OrderManagerInterface::class,
            'points.manager' => \App\Plugins\Contracts\PointsManagerInterface::class,
            'cart.manager' => \App\Plugins\Contracts\CartManagerInterface::class,
            'payment.gateway' => \App\Plugins\Contracts\PaymentGatewayInterface::class,
            'payment.reconciler' => \App\Plugins\Contracts\PaymentReconcilerInterface::class,
            'notification.channel' => \App\Plugins\Contracts\NotificationChannelInterface::class,
            'form.type' => \App\Plugins\Contracts\FormTypeProviderInterface::class,
            'file.converter' => \App\Plugins\Contracts\FileConverterInterface::class,
            'data.importer' => \App\Plugins\Contracts\DataImporterInterface::class,
            'data.exporter' => \App\Plugins\Contracts\DataExporterInterface::class,
            'data.collector' => \App\Plugins\Contracts\DataCollectorInterface::class,
        ];
        $this->registerAutoload($manifest, rtrim($directory, '/'));
        $lifecycle = trim((string)($manifest['lifecycle'] ?? ''));
        if ($lifecycle !== '') {
            if (!class_exists($lifecycle)) {
                $errors[] = ['code' => 'contract.lifecycle_class', 'path' => 'lifecycle', 'message' => "生命周期类不存在：{$lifecycle}", 'suggestion' => '检查 PSR-4 命名空间和类文件路径'];
            } elseif (!is_subclass_of($lifecycle, PluginInterface::class)) {
                $errors[] = ['code' => 'contract.lifecycle_interface', 'path' => 'lifecycle', 'message' => "{$lifecycle} 必须实现 PluginInterface", 'suggestion' => '实现 activate、deactivate、uninstall 方法'];
            }
        }
        foreach (['routes', 'events', 'filters', 'slots', 'jobs'] as $section) {
            foreach (($manifest[$section] ?? []) as $index => $definition) {
                if (!is_array($definition) || empty($definition['handler'])) continue;
                $this->validateHandlerContract((string)$definition['handler'], "{$section}.{$index}.handler", $errors);
                foreach ((array)($definition['middleware'] ?? []) as $middlewareIndex => $middleware) {
                    if (is_string($middleware) && str_contains($middleware, '@')) $this->validateHandlerContract($middleware, "{$section}.{$index}.middleware.{$middlewareIndex}", $errors);
                }
            }
        }
        foreach (($manifest['services'] ?? []) as $index => $definition) {
            if (!is_array($definition)) continue;
            $class = trim((string)($definition['class'] ?? ''));
            if ($class === '' || !class_exists($class)) {
                $errors[] = ['code' => 'contract.service_class', 'path' => "services.{$index}.class", 'message' => "服务类不存在：{$class}", 'suggestion' => '检查服务类名与 PSR-4 路径'];
                continue;
            }
            $serviceId = is_string($index) ? $index : (string)($definition['id'] ?? $definition['name'] ?? '');
            if (isset($serviceContracts[$serviceId]) && !is_subclass_of($class, $serviceContracts[$serviceId])) {
                $errors[] = ['code' => 'contract.service_interface', 'path' => "services.{$index}.class", 'message' => "{$class} 必须实现 {$serviceContracts[$serviceId]}", 'suggestion' => '使用 Plugin SDK 中对应的标准服务接口'];
            }
        }
        return $errors;
    }

    private function validateHandlerContract(string $handler, string $path, array &$errors): void {
        try {
            [$class, $method] = \App\Plugins\Support\Handler::parse($handler);
            if (!class_exists($class)) throw new \RuntimeException("处理器类不存在：{$class}");
            if (!method_exists($class, $method) || !(new \ReflectionMethod($class, $method))->isPublic()) throw new \RuntimeException("处理器方法不存在或不可公开调用：{$handler}");
        } catch (\Throwable $e) {
            $errors[] = ['code' => 'contract.handler', 'path' => $path, 'message' => $e->getMessage(), 'suggestion' => '处理器必须使用可自动加载的 Public Class@method'];
        }
    }

    private function assertDependencies(string $pluginId, array $manifest): void {
        foreach (($manifest['dependencies'] ?? []) as $dependency) {
            $id = is_array($dependency) ? (string)($dependency['id'] ?? '') : (string)$dependency;
            $constraint = is_array($dependency) ? (string)($dependency['version'] ?? '*') : '*';
            $row = $this->registry->find($id);
            if (!$row || $row['status'] !== 'enabled') throw new \RuntimeException("依赖插件未启用：{$id}");
            if (!PluginManifest::satisfies((string)$row['active_version'], $constraint)) throw new \RuntimeException("依赖插件版本不满足：{$id} {$constraint}");
        }
        foreach (($manifest['conflicts'] ?? []) as $conflict) {
            $id = is_array($conflict) ? (string)($conflict['id'] ?? '') : (string)$conflict;
            if (($this->registry->find($id)['status'] ?? '') === 'enabled') throw new \RuntimeException("与已启用插件冲突：{$id}");
        }
    }

    private function assertRequiredServices(string $pluginId, array $manifest): void {
        $available = [];
        foreach ($this->registry->enabled() as $row) {
            if ($row['plugin_id'] === $pluginId) continue;
            foreach (($row['manifest']['services'] ?? []) as $name => $service) {
                if (!is_array($service)) continue;
                $id = is_string($name) ? $name : (string)($service['id'] ?? $service['name'] ?? '');
                if ($id === '') continue;
                $available[$id][] = [
                    'provider' => (string)($service['provider'] ?? $row['plugin_id']),
                    'version' => (string)($service['version'] ?? '1.0.0'),
                ];
            }
        }
        foreach (($manifest['requires_services'] ?? []) as $name => $requirement) {
            if (is_string($name)) {
                $id = $name;
                $constraint = is_array($requirement) ? (string)($requirement['version'] ?? '*') : (string)$requirement;
                $provider = is_array($requirement) ? (string)($requirement['provider'] ?? '') : '';
            } else {
                $id = (string)($requirement['id'] ?? $requirement['name'] ?? '');
                $constraint = (string)($requirement['version'] ?? '*');
                $provider = (string)($requirement['provider'] ?? '');
            }
            $matches = array_filter($available[$id] ?? [], static fn(array $service): bool => ($provider === '' || $service['provider'] === $provider) && PluginManifest::satisfies($service['version'], $constraint));
            if ($id === '' || $matches === []) throw new \RuntimeException("缺少兼容服务：{$id} {$constraint}" . ($provider !== '' ? " provider={$provider}" : ''));
        }
    }

    private function assertNoRouteConflicts(string $pluginId, array $manifest): void {
        $claimed = [];
        $claimedNames = [];
        foreach ($this->registry->enabled() as $row) {
            if ($row['plugin_id'] === $pluginId) continue;
            foreach (($row['manifest']['routes'] ?? []) as $route) {
                $claimed[strtoupper((string)($route['method'] ?? 'GET')) . ' ' . $this->routeShape((string)($route['path'] ?? ''))] = $row['plugin_id'];
                $name = trim((string)($route['name'] ?? ''));
                if ($name !== '') $claimedNames[$name] = $row['plugin_id'];
            }
        }
        foreach (($manifest['routes'] ?? []) as $route) {
            $path = (string)$route['path'];
            foreach (self::RESERVED_PREFIXES as $reserved) {
                if ($path === $reserved || str_starts_with($path, rtrim($reserved, '/') . '/')) throw new \RuntimeException("插件路由使用核心保留路径：{$path}");
            }
            if (in_array($this->routeShape($path), self::RESERVED_ROUTES, true)) throw new \RuntimeException("插件路由使用核心保留路径：{$path}");
            $key = strtoupper((string)($route['method'] ?? 'GET')) . ' ' . $this->routeShape($path);
            if (isset($claimed[$key])) throw new \RuntimeException("插件路由与 {$claimed[$key]} 冲突：{$key}");
            $name = trim((string)($route['name'] ?? ''));
            if ($name !== '' && isset($claimedNames[$name])) throw new \RuntimeException("插件路由名称与 {$claimedNames[$name]} 冲突：{$name}");
        }
    }

    private function routeShape(string $path): string {
        return preg_replace('/(?::[a-zA-Z_][a-zA-Z0-9_]*|\{[a-zA-Z_][a-zA-Z0-9_]*\})/', '{}', $path) ?? $path;
    }

    private function assertNoServiceConflicts(string $pluginId, array $manifest): void {
        $single = ['member.identity', 'order.manager', 'points.manager', 'cart.manager'];
        $claimed = [];
        foreach ($this->registry->enabled() as $row) {
            if ($row['plugin_id'] === $pluginId) continue;
            foreach (($row['manifest']['services'] ?? []) as $name => $service) {
                $id = is_string($name) ? $name : (string)($service['id'] ?? $service['name'] ?? '');
                if (in_array($id, $single, true)) $claimed[$id] = $row['plugin_id'];
            }
        }
        foreach (($manifest['services'] ?? []) as $name => $service) {
            $id = is_string($name) ? $name : (string)($service['id'] ?? $service['name'] ?? '');
            if (isset($claimed[$id])) throw new \RuntimeException("单提供者服务 {$id} 已由 {$claimed[$id]} 提供");
        }
    }

    private function runMigrations(string $pluginId, string $version, array $manifest, string $base): void {
        $directory = $base . '/migrations';
        if (!is_dir($directory)) return;
        $files = glob($directory . '/*.php') ?: [];
        sort($files, SORT_STRING);
        $database = new PluginDatabase($pluginId, $this->db);
        $schema = new PluginSchema($database);
        $context = $this->standaloneContext($pluginId, $version, $base);
        foreach ($files as $file) {
            $migrationVersion = pathinfo($file, PATHINFO_FILENAME);
            $stmt = $this->db->prepare('SELECT 1 FROM plugin_migrations WHERE plugin_id=:id AND version=:version');
            $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
            $stmt->bindValue(':version', $migrationVersion, SQLITE3_TEXT);
            if ($stmt->execute()?->fetchArray(SQLITE3_NUM)) continue;
            $started = microtime(true);
            $this->db->exec('BEGIN IMMEDIATE');
            try {
                $migration = require $file;
                if (!is_object($migration) || !method_exists($migration, 'up')) throw new \RuntimeException("无效插件迁移：{$migrationVersion}");
                $migration->up($schema, $context);
                $record = $this->db->prepare('INSERT INTO plugin_migrations(plugin_id,version,name,executed_at,execution_time) VALUES(:id,:version,:name,:now,:time)');
                $record->bindValue(':id', $pluginId, SQLITE3_TEXT);
                $record->bindValue(':version', $migrationVersion, SQLITE3_TEXT);
                $record->bindValue(':name', basename($file), SQLITE3_TEXT);
                $record->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
                $record->bindValue(':time', (int)((microtime(true) - $started) * 1000), SQLITE3_INTEGER);
                $record->execute();
                $this->db->exec('COMMIT');
            } catch (\Throwable $e) {
                $this->db->exec('ROLLBACK');
                throw $e;
            }
        }
    }

    private function invokeLifecycle(array $manifest, string $method, PluginContext $context): void {
        $class = (string)($manifest['lifecycle'] ?? '');
        if ($class === '') return;
        if (!class_exists($class)) throw new \RuntimeException("插件生命周期类不存在：{$class}");
        $instance = new $class();
        if (!$instance instanceof PluginInterface) throw new \RuntimeException("{$class} 必须实现 PluginInterface");
        PluginRuntime::beginInvocation($context->pluginId());
        try { $instance->$method($context); } finally { PluginRuntime::endInvocation(); }
    }

    private function seedScheduledJobs(PluginContext $context, array $manifest): void {
        $scheduled = [];
        foreach (($manifest['jobs'] ?? []) as $name => $job) {
            if (!is_array($job) || empty($job['handler'])) continue;
            $interval = (int)($job['interval'] ?? 0);
            if ($interval >= 60) {
                $scheduled[] = 'schedule:' . $name;
                $context->jobs()->schedule((string)$name, (string)$job['handler'], $interval, (array)($job['payload'] ?? []));
            }
        }
        $stmt = $this->db->prepare("SELECT id,idempotency_key FROM plugin_jobs WHERE plugin_id=:id AND substr(idempotency_key,1,9)='schedule:' AND status IN ('pending','retry','failed')");
        $stmt->bindValue(':id', $context->pluginId(), SQLITE3_TEXT);
        $result = $stmt->execute();
        $obsolete = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (!in_array($row['idempotency_key'], $scheduled, true)) $obsolete[] = (int)$row['id'];
        }
        $result->finalize();
        foreach ($obsolete as $id) {
            $cancel = $this->db->prepare("UPDATE plugin_jobs SET status='cancelled',updated_at=:now WHERE id=:id");
            $cancel->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
            $cancel->bindValue(':id', $id, SQLITE3_INTEGER);
            $cancel->execute();
        }
    }

    private function standaloneContext(string $id, string $version, string $base): PluginContext {
        $runtime = PluginRuntime::boot();
        return new PluginContext($id, $version, $base, $runtime);
    }

    private function registerAutoload(array $manifest, string $base): void {
        $map = $manifest['autoload']['psr4'];
        spl_autoload_register(static function (string $class) use ($map, $base): void {
            foreach ($map as $prefix => $path) {
                if (!str_starts_with($class, $prefix)) continue;
                $file = $base . '/' . trim((string)$path, '/') . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($file)) require $file;
                return;
            }
        }, true, true);
        if (is_file($base . '/vendor/autoload.php')) require_once $base . '/vendor/autoload.php';
    }

    private function publishAssets(string $id, string $base): void {
        $source = $base . '/assets';
        if (!is_dir($source)) return;
        $destination = PluginFiles::assets($id);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || !in_array(strtolower($file->getExtension()), self::ALLOWED_ASSET_EXTENSIONS, true)) continue;
            $relative = substr($file->getPathname(), strlen($source) + 1);
            $target = $destination . '/' . $relative;
            PluginFiles::mkdir(dirname($target));
            if (!copy($file->getPathname(), $target)) throw new \RuntimeException('无法发布插件资源');
        }
    }

    private function backupDatabase(string $target): void {
        $backup = new SQLite3($target);
        try { if (!$this->db->backup($backup)) throw new \RuntimeException('无法创建插件数据库快照'); }
        finally { $backup->close(); }
    }

    private function extractZip(string $zipPath, string $destination): void {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) throw new \RuntimeException('无法打开插件 ZIP');
        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_ENTRIES) throw new \RuntimeException('插件 ZIP 文件数量异常');
            $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $name = str_replace('\\', '/', (string)($stat['name'] ?? ''));
                if ($name === '' || str_contains($name, "\0") || str_starts_with($name, '/') || in_array('..', explode('/', trim($name, '/')), true)) throw new \RuntimeException('插件 ZIP 包含非法路径');
                $total += (int)($stat['size'] ?? 0);
                if ((int)($stat['size'] ?? 0) > self::MAX_FILE_BYTES || $total > self::MAX_TOTAL_BYTES) throw new \RuntimeException('插件 ZIP 解压体积过大');
                $os = $attrs = 0;
                if ($zip->getExternalAttributesIndex($i, $os, $attrs) && (($attrs >> 16) & 0xF000) === 0xA000) throw new \RuntimeException('插件 ZIP 不允许符号链接');
                $target = $destination . '/' . trim($name, '/');
                if (str_ends_with($name, '/')) { if (!is_dir($target)) mkdir($target, 0755, true); continue; }
                if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
                $input = $zip->getStream((string)$stat['name']);
                $output = fopen($target, 'xb');
                if ($input === false || $output === false) throw new \RuntimeException('无法解压插件文件');
                stream_copy_to_stream($input, $output, self::MAX_FILE_BYTES + 1);
                fclose($input); fclose($output);
            }
        } finally { $zip->close(); }
    }

    private function locatePackage(string $workspace): string {
        if (is_file($workspace . '/plugin.json')) return $workspace;
        $matches = glob($workspace . '/*/plugin.json') ?: [];
        if (count($matches) !== 1) throw new \RuntimeException('ZIP 必须只包含一个插件包');
        return dirname($matches[0]);
    }

    private function tempDirectory(string $prefix): string {
        $base = APP_ROOT . '/storage/tmp/plugins';
        if (!is_dir($base)) mkdir($base, 0755, true);
        $directory = $base . '/' . $prefix . bin2hex(random_bytes(8));
        if (!mkdir($directory, 0700, true)) throw new \RuntimeException('无法创建插件临时目录');
        return $directory;
    }

    private function removeDirectory(string $directory): void {
        PluginFiles::remove($directory);
    }
}
