<?php
declare(strict_types=1);

use App\Plugins\PluginCache;
use App\Plugins\PluginFiles;
use App\Plugins\PluginManifest;
use App\Plugins\PluginOperationLock;
use App\Plugins\PluginRegistry;

/** Convert installed version trees once, preserving the version actually in use. */
return new class {
    public function up(SQLite3 $db): void {
        PluginOperationLock::exclusive(function () use ($db): void {
            $db->enableExceptions(true);
            $registry = new PluginRegistry($db);
            $workspace = PluginFiles::workspace();
            $replacements = [];
            $cleanup = true;
            $db->exec('BEGIN IMMEDIATE');
            try {
                foreach ($registry->all() as $row) {
                    $id = (string)$row['plugin_id'];
                    $root = PluginFiles::code($id);
                    if ($row['status'] === 'removed') continue;
                    $enabled = $row['status'] === 'enabled';
                    $version = (string)($enabled && $row['active_version'] ? $row['active_version'] : $row['installed_version']);
                    if (is_file($root . '/plugin.json')) continue;
                    if (preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/D', $version) !== 1) throw new RuntimeException('旧插件版本无效：' . $id);
                    $source = $root . '/' . $version;
                    $validation = PluginManifest::validateFile($source . '/plugin.json');
                    if (!$validation['valid'] || $validation['manifest']['id'] !== $id || $validation['manifest']['version'] !== $version) {
                        throw new RuntimeException('找不到有效的旧插件代码，迁移已停止：' . $id . ' ' . $version);
                    }
                    $stage = $workspace . '/' . $id;
                    PluginFiles::copy($source, $stage . '/code');
                    $assets = PluginFiles::assets($id);
                    if (is_link($root) || is_link($assets)) throw new RuntimeException('旧插件目录不允许符号链接');
                    $hasAssets = is_dir($assets . '/' . $version);
                    if ($hasAssets) PluginFiles::copy($assets . '/' . $version, $stage . '/assets');
                    $this->replace($root, $stage . '/code', $stage . '/previous-code', $replacements);
                    if (is_dir($assets)) {
                        // Remove all historical asset trees, retaining only selected assets.
                        $this->replace($assets, $hasAssets ? $stage . '/assets' : null, $stage . '/previous-assets', $replacements);
                    }
                    $registry->save($validation['manifest'], (string)$row['source']);
                    $registry->setStatus($id, (string)$row['status'], $enabled ? $version : null);
                    $registry->log($id, 'info', '插件已迁移到固定目录，保留版本 ' . $version);
                }
                (new PluginCache())->rebuild($registry->enabled());
                $db->exec('COMMIT');
            } catch (Throwable $e) {
                $db->exec('ROLLBACK');
                try {
                    foreach (array_reverse($replacements) as [$destination, $backup]) {
                        PluginFiles::invalidate($destination);
                        PluginFiles::remove($destination);
                        PluginFiles::move($backup, $destination);
                        PluginFiles::invalidate($destination);
                    }
                    // Never leave a cache pointing at a partially migrated tree.
                    foreach (['plugins.php', 'plugins.php.last.php'] as $file) {
                        $path = APP_ROOT . '/storage/cache/' . $file;
                        if (is_file($path)) {
                            if (!unlink($path)) throw new RuntimeException('无法清理插件迁移缓存');
                            if (function_exists('opcache_invalidate')) @opcache_invalidate($path, true);
                        }
                    }
                } catch (Throwable $recovery) {
                    $cleanup = false;
                    throw new RuntimeException('插件目录迁移失败且恢复未完成，恢复文件保留于 ' . $workspace . '：' . $recovery->getMessage(), 0, $e);
                }
                throw $e;
            } finally {
                if ($cleanup) {
                    try { PluginFiles::remove($workspace); }
                    catch (Throwable $e) { error_log('[Plugin Migration Cleanup] ' . $e->getMessage()); }
                }
            }
        });
    }

    private function replace(string $destination, ?string $source, string $backup, array &$replacements): void {
        PluginFiles::invalidate($destination);
        PluginFiles::move($destination, $backup);
        $replacements[] = [$destination, $backup];
        if ($source !== null) PluginFiles::move($source, $destination);
        PluginFiles::invalidate($destination);
    }

    public function down(SQLite3 $db): void {
        throw new RuntimeException('固定插件目录迁移不可逆；需要恢复升级前的完整备份');
    }
};
