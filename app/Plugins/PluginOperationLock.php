<?php
declare(strict_types=1);

namespace App\Plugins;

/** Keep requests using plugin code out of a directory replacement. */
final class PluginOperationLock {
    private static mixed $handle = null;
    private static bool $request = false;
    private static int $depth = 0;
    private static bool $changed = false;

    public static function beginRequest(): void {
        if (self::$handle !== null) return;
        self::$handle = self::open();
        if (!flock(self::$handle, LOCK_SH)) throw new \RuntimeException('无法获取插件读取锁');
        self::$request = true;
    }

    public static function changed(): bool { return self::$changed; }

    public static function exclusive(callable $operation): mixed {
        if (self::$depth > 0) return $operation();
        $handle = self::$handle ?? self::open();
        if (self::$request) flock($handle, LOCK_UN);
        if (!flock($handle, LOCK_EX)) throw new \RuntimeException('无法获取插件操作锁');
        self::$depth++;
        try {
            return $operation();
        } finally {
            self::$depth--;
            self::$changed = true;
            // Retain the writer lock until this request (including shutdown) ends.
            if (!self::$request) { flock($handle, LOCK_UN); fclose($handle); }
        }
    }

    private static function open(): mixed {
        $directory = APP_ROOT . '/storage/locks';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('无法创建插件锁目录');
        $handle = fopen($directory . '/plugins.lock', 'c');
        if ($handle === false) throw new \RuntimeException('无法打开插件操作锁');
        return $handle;
    }
}
