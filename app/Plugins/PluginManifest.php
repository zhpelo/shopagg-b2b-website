<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginManifest {
    public const SCHEMA_VERSION = 1;

    /** @return array{valid:bool,errors:array<int,array{code:string,path:string,message:string}>,manifest:array} */
    public static function validateFile(string $file): array {
        if (!is_file($file)) {
            return self::result([], [['code' => 'manifest.missing', 'path' => 'plugin.json', 'message' => '缺少 plugin.json']]);
        }
        $raw = file_get_contents($file);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($data)) {
            return self::result([], [['code' => 'manifest.json', 'path' => 'plugin.json', 'message' => 'plugin.json 不是有效 JSON']]);
        }
        return self::validate($data);
    }

    /** @return array{valid:bool,errors:array,manifest:array} */
    public static function validate(array $manifest): array {
        $errors = [];
        $required = ['schema_version', 'id', 'name', 'vendor', 'version', 'description', 'requires', 'autoload'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $manifest)) {
                $errors[] = ['code' => 'manifest.required', 'path' => $key, 'message' => "缺少必填字段 {$key}"];
            }
        }
        if ((int)($manifest['schema_version'] ?? 0) !== self::SCHEMA_VERSION) {
            $errors[] = ['code' => 'manifest.schema_version', 'path' => 'schema_version', 'message' => '不支持的 Manifest Schema 版本'];
        }
        $id = (string)($manifest['id'] ?? '');
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $id) !== 1 || strlen($id) > 64) {
            $errors[] = ['code' => 'manifest.id', 'path' => 'id', 'message' => '插件 ID 仅允许小写字母、数字和中间连字符，最长 64 字符'];
        }
        if (!self::validVersion((string)($manifest['version'] ?? ''))) {
            $errors[] = ['code' => 'manifest.version', 'path' => 'version', 'message' => '插件版本必须使用 SemVer'];
        }
        if (!is_array($manifest['requires'] ?? null)) {
            $errors[] = ['code' => 'manifest.requires', 'path' => 'requires', 'message' => 'requires 必须是对象'];
        }
        $psr4 = $manifest['autoload']['psr4'] ?? null;
        if (!is_array($psr4) || $psr4 === []) {
            $errors[] = ['code' => 'manifest.autoload', 'path' => 'autoload.psr4', 'message' => '至少声明一个 PSR-4 命名空间'];
        } else {
            foreach ($psr4 as $prefix => $path) {
                if (!is_string($prefix) || !str_ends_with($prefix, '\\') || !self::safeRelativePath((string)$path)) {
                    $errors[] = ['code' => 'manifest.autoload_entry', 'path' => 'autoload.psr4', 'message' => 'PSR-4 声明无效'];
                }
            }
        }
        foreach (['routes', 'events', 'filters', 'slots', 'services', 'provides_services', 'requires_services', 'jobs', 'permissions', 'admin_menu', 'assets', 'dependencies', 'conflicts', 'settings'] as $key) {
            if (isset($manifest[$key]) && !is_array($manifest[$key])) {
                $errors[] = ['code' => 'manifest.array', 'path' => $key, 'message' => "{$key} 必须是数组或对象"];
            }
        }
        $routeKeys = [];
        $routeNames = [];
        foreach (($manifest['routes'] ?? []) as $index => $route) {
            $method = strtoupper((string)($route['method'] ?? 'GET'));
            $path = (string)($route['path'] ?? '');
            if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true) || !str_starts_with($path, '/') || empty($route['handler'])) {
                $errors[] = ['code' => 'manifest.route', 'path' => "routes.{$index}", 'message' => '路由必须包含有效 method、path 和 handler'];
            }
            $shape = preg_replace('/(?::[a-zA-Z_][a-zA-Z0-9_]*|\{[a-zA-Z_][a-zA-Z0-9_]*\})/', '{}', $path);
            $key = $method . ' ' . $shape;
            if (isset($routeKeys[$key])) $errors[] = ['code' => 'manifest.route_duplicate', 'path' => "routes.{$index}", 'message' => "插件内路由冲突：{$key}"];
            $routeKeys[$key] = true;
            $name = trim((string)($route['name'] ?? ''));
            if ($name !== '' && isset($routeNames[$name])) $errors[] = ['code' => 'manifest.route_name_duplicate', 'path' => "routes.{$index}.name", 'message' => "插件内路由名称重复：{$name}"];
            if ($name !== '') $routeNames[$name] = true;
        }
        $normalized = self::normalize($manifest);
        return self::result($normalized, $errors);
    }

    public static function environmentErrors(array $manifest): array {
        $errors = [];
        $requires = $manifest['requires'] ?? [];
        $php = (string)($requires['php'] ?? '>=8.1.0');
        if (!self::satisfies(PHP_VERSION, $php)) {
            $errors[] = "PHP " . PHP_VERSION . " 不满足 {$php}";
        }
        $shopagg = (string)($requires['shopagg'] ?? '>=1.0.0');
        if (defined('APP_VERSION') && !self::satisfies((string)APP_VERSION, $shopagg)) {
            $errors[] = 'ShopAgg ' . APP_VERSION . " 不满足 {$shopagg}";
        }
        foreach (($requires['extensions'] ?? []) as $extension) {
            if (!extension_loaded((string)$extension)) {
                $errors[] = "缺少 PHP 扩展 {$extension}";
            }
        }
        foreach (($requires['functions'] ?? []) as $function) {
            if (!function_exists((string)$function)) {
                $errors[] = "缺少 PHP 函数 {$function}";
            }
        }
        foreach (($requires['commands'] ?? []) as $command) {
            $command = trim((string)$command);
            if ($command !== '' && !self::commandExists($command)) $errors[] = "缺少系统命令 {$command}";
        }
        return $errors;
    }

    public static function satisfies(string $version, string $constraint): bool {
        $constraint = trim($constraint);
        if ($constraint === '' || $constraint === '*') return true;
        foreach (preg_split('/\s*,\s*|\s+/', $constraint) ?: [] as $part) {
            if ($part === '') continue;
            if ($part[0] === '^') {
                $base = substr($part, 1);
                $segments = array_map('intval', array_pad(explode('.', $base), 3, '0'));
                [$major, $minor, $patch] = $segments;
                $upper = $major > 0 ? ($major + 1) . '.0.0' : ($minor > 0 ? '0.' . ($minor + 1) . '.0' : '0.0.' . ($patch + 1));
                if (!version_compare($version, $base, '>=') || !version_compare($version, $upper, '<')) return false;
                continue;
            }
            if (preg_match('/^(>=|<=|>|<|=|==)?\s*(\d+(?:\.\d+){1,2}(?:[-+][0-9A-Za-z.-]+)?)$/', $part, $m)) {
                if (!version_compare($version, $m[2], $m[1] ?: '=')) return false;
                continue;
            }
            return false;
        }
        return true;
    }

    public static function safeRelativePath(string $path): bool {
        $path = str_replace('\\', '/', trim($path));
        return $path !== '' && !str_starts_with($path, '/') && !str_contains($path, "\0")
            && !in_array('..', explode('/', trim($path, '/')), true);
    }

    private static function validVersion(string $version): bool {
        return preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/D', $version) === 1;
    }

    private static function normalize(array $m): array {
        foreach (['routes', 'events', 'filters', 'slots', 'services', 'provides_services', 'requires_services', 'jobs', 'permissions', 'admin_menu', 'assets', 'dependencies', 'conflicts'] as $key) {
            $m[$key] = is_array($m[$key] ?? null) ? $m[$key] : [];
        }
        $m['services'] = array_replace($m['provides_services'], $m['services']);
        $m['settings'] = is_array($m['settings'] ?? null) ? $m['settings'] : [];
        return $m;
    }

    private static function result(array $manifest, array $errors): array {
        return ['valid' => $errors === [], 'errors' => $errors, 'manifest' => $manifest];
    }

    private static function commandExists(string $command): bool {
        if (str_contains($command, '/') || str_contains($command, '\\')) return is_file($command) && is_executable($command);
        foreach (explode(PATH_SEPARATOR, (string)getenv('PATH')) as $directory) {
            if ($directory !== '' && is_file(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $command)
                && is_executable(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $command)) return true;
        }
        return false;
    }
}
