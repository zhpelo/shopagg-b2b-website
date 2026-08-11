<?php
declare(strict_types=1);

namespace App\Plugins\Support;

final class Handler {
    public static function parse(string|array $handler): array {
        if (is_array($handler) && count($handler) === 2) return array_values($handler);
        $parts = explode('@', (string)$handler, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new \InvalidArgumentException('处理器必须使用 Class@method 格式');
        }
        return $parts;
    }

    public static function invoke(string|array $handler, array $arguments = []): mixed {
        [$class, $method] = self::parse($handler);
        if (!class_exists($class)) throw new \RuntimeException("处理器类不存在：{$class}");
        $instance = new $class();
        if (!is_callable([$instance, $method])) throw new \RuntimeException("处理器方法不可调用：{$class}@{$method}");
        return $instance->$method(...$arguments);
    }
}
