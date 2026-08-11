<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginCache {
    private string $file;

    public function __construct(?string $file = null) {
        $this->file = $file ?? APP_ROOT . '/storage/cache/plugins.php';
    }

    public function load(): array {
        if (!is_file($this->file)) return self::empty();
        try {
            $cache = require $this->file;
            if (is_array($cache)) return array_replace_recursive(self::empty(), $cache);
        } catch (\Throwable $e) {
            error_log('[Plugin Cache] 主缓存无效：' . $e->getMessage());
        }
        $fallback = $this->file . '.last.php';
        if (!is_file($fallback)) return self::empty();
        try {
            $cache = require $fallback;
            return is_array($cache) ? array_replace_recursive(self::empty(), $cache) : self::empty();
        } catch (\Throwable $e) {
            error_log('[Plugin Cache] 备用缓存无效：' . $e->getMessage());
            return self::empty();
        }
    }

    public function rebuild(array $plugins): array {
        $cache = self::empty();
        foreach ($this->sortDependencies($plugins) as $row) {
            $m = $row['manifest'];
            $id = (string)$m['id'];
            $version = (string)$row['active_version'];
            $base = APP_ROOT . '/storage/plugins/' . $id . '/' . $version;
            $cache['plugins'][$id] = ['id' => $id, 'version' => $version, 'base' => $base, 'manifest' => $m];
            foreach (($m['autoload']['psr4'] ?? []) as $prefix => $path) {
                $cache['autoload'][$prefix] = $base . '/' . trim((string)$path, '/');
            }
            foreach (['routes', 'events', 'filters', 'slots', 'services', 'jobs', 'admin_menu', 'permissions', 'assets'] as $key) {
                foreach (($m[$key] ?? []) as $name => $item) {
                    if (!is_array($item)) $item = ['value' => $item];
                    $item['plugin_id'] = $id;
                    $item['plugin_version'] = $version;
                    if (in_array($key, ['events', 'filters', 'slots', 'services', 'jobs'], true) && is_string($name)) {
                        $item['name'] = $item['name'] ?? $name;
                    }
                    $cache[$key][] = $item;
                }
            }
        }
        foreach (['events', 'filters', 'slots'] as $key) {
            usort($cache[$key], static fn(array $a, array $b): int => [(int)($a['priority'] ?? 100), $a['plugin_id']] <=> [(int)($b['priority'] ?? 100), $b['plugin_id']]);
        }
        $directory = dirname($this->file);
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('无法创建插件缓存目录');
        }
        $tmp = $this->file . '.' . bin2hex(random_bytes(6)) . '.tmp';
        $content = "<?php\ndeclare(strict_types=1);\nreturn " . var_export($cache, true) . ";\n";
        if (file_put_contents($tmp, $content, LOCK_EX) === false) {
            @unlink($tmp);
            throw new \RuntimeException('无法原子更新插件缓存');
        }
        if (is_file($this->file)) {
            $fallbackTmp = $this->file . '.last.' . bin2hex(random_bytes(4)) . '.tmp';
            if (copy($this->file, $fallbackTmp)) rename($fallbackTmp, $this->file . '.last.php');
            else @unlink($fallbackTmp);
        }
        if (!rename($tmp, $this->file)) { @unlink($tmp); throw new \RuntimeException('无法原子更新插件缓存'); }
        $fallbackCurrent = $this->file . '.last.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($fallbackCurrent, $content, LOCK_EX) !== false) rename($fallbackCurrent, $this->file . '.last.php');
        else @unlink($fallbackCurrent);
        if (function_exists('opcache_invalidate')) @opcache_invalidate($this->file, true);
        return $cache;
    }

    public function file(): string { return $this->file; }

    public static function empty(): array {
        return ['plugins' => [], 'autoload' => [], 'routes' => [], 'events' => [], 'filters' => [], 'slots' => [], 'services' => [], 'jobs' => [], 'admin_menu' => [], 'permissions' => [], 'assets' => []];
    }

    private function sortDependencies(array $plugins): array {
        $byId = [];
        foreach ($plugins as $row) $byId[$row['plugin_id']] = $row;
        $sorted = [];
        $visiting = [];
        $visit = function (string $id) use (&$visit, &$sorted, &$visiting, $byId): void {
            if (isset($sorted[$id])) return;
            if (isset($visiting[$id])) throw new \RuntimeException("插件依赖形成循环：{$id}");
            $visiting[$id] = true;
            foreach (($byId[$id]['manifest']['dependencies'] ?? []) as $dep) {
                $depId = is_array($dep) ? (string)($dep['id'] ?? '') : (string)$dep;
                if ($depId !== '' && isset($byId[$depId])) $visit($depId);
            }
            unset($visiting[$id]);
            $sorted[$id] = $byId[$id];
        };
        $ids = array_keys($byId);
        usort($ids, static fn(string $a, string $b): int => [(int)($byId[$a]['manifest']['load_order'] ?? 100), $a] <=> [(int)($byId[$b]['manifest']['load_order'] ?? 100), $b]);
        foreach ($ids as $id) $visit($id);
        return array_values($sorted);
    }
}
