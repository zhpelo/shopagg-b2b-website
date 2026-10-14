<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginFiles {
    public static function code(string $id): string { return APP_ROOT . '/storage/plugins/' . self::id($id); }
    public static function assets(string $id): string { return APP_ROOT . '/assets/plugins/' . self::id($id); }

    private static function id(string $id): string {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id) !== 1 || strlen($id) > 64) throw new \InvalidArgumentException('插件 ID 无效');
        return $id;
    }

    public static function workspace(): string {
        $directory = APP_ROOT . '/storage/tmp/plugins/update-' . bin2hex(random_bytes(8));
        self::mkdir($directory);
        chmod($directory, 0700);
        return $directory;
    }

    public static function mkdir(string $directory): void {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('无法创建插件目录：' . $directory);
    }

    public static function copy(string $source, string $destination): void {
        if (is_link($source)) throw new \RuntimeException('插件目录不允许符号链接');
        self::mkdir($destination);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->isLink()) throw new \RuntimeException('插件目录不允许符号链接');
            $target = $destination . '/' . $iterator->getSubPathName();
            if ($item->isDir()) self::mkdir($target);
            elseif (!copy($item->getPathname(), $target)) throw new \RuntimeException('无法复制插件文件');
        }
    }

    public static function move(string $source, string $destination): void {
        self::mkdir(dirname($destination));
        if (file_exists($destination) || is_link($destination) || !rename($source, $destination)) throw new \RuntimeException('无法替换插件目录：' . $destination);
        clearstatcache();
    }

    public static function remove(string $directory): void {
        if (is_link($directory)) { if (!unlink($directory)) throw new \RuntimeException('无法移除插件符号链接'); return; }
        if (!is_dir($directory)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $removed = $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            if (!$removed) throw new \RuntimeException('无法清理插件文件：' . $item->getPathname());
        }
        if (!rmdir($directory)) throw new \RuntimeException('无法清理插件目录：' . $directory);
        clearstatcache();
    }

    public static function invalidate(string $directory): void {
        if (!function_exists('opcache_invalidate') || !is_dir($directory)) return;
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $item) {
            if ($item->isFile() && !$item->isLink() && strtolower($item->getExtension()) === 'php') @opcache_invalidate($item->getPathname(), true);
        }
    }
}
