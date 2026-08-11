<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\SiteController;
use App\Plugins\Http\Request;
use App\Plugins\Http\Response;
use App\Plugins\PluginRuntime;
use App\Plugins\Support\Handler;

final class Router {
    private array $routes = [];
    private array $namedRoutes = [];
    private int $sequence = 0;
    private array $groupStack = [];

    public function add(string $method, string $path, mixed $callback, array $options = []): void {
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) throw new \InvalidArgumentException("不支持的 HTTP 方法：{$method}");
        if (!str_starts_with($path, '/')) throw new \InvalidArgumentException('路由路径必须以 / 开头');
        $group = $this->mergedGroup();
        $path = rtrim((string)($group['prefix'] ?? ''), '/') . $path;
        if ($path === '') $path = '/';
        $name = $options['name'] ?? null;
        if (is_string($name) && $name !== '') {
            if (isset($this->namedRoutes[$name])) throw new \RuntimeException("路由名称重复：{$name}");
            $this->namedRoutes[$name] = $path;
        }
        $parameterNames = [];
        $tokens = [];
        $template = preg_replace_callback('/(?::([a-zA-Z_][a-zA-Z0-9_]*)|\{([a-zA-Z_][a-zA-Z0-9_]*)\})/', static function (array $matches) use (&$parameterNames, &$tokens): string {
            $name = $matches[1] !== '' ? $matches[1] : $matches[2];
            $parameterNames[] = $name;
            $token = '__SHOPAGG_PARAM_' . count($tokens) . '__';
            $tokens[$token] = '(?P<' . $name . '>[a-zA-Z0-9._~-]+)';
            return $token;
        }, $path) ?? $path;
        $pattern = preg_quote($template, '#');
        foreach ($tokens as $token => $replacement) $pattern = str_replace(preg_quote($token, '#'), $replacement, $pattern);
        $middleware = array_values(array_merge((array)($group['middleware'] ?? []), (array)($options['middleware'] ?? [])));
        $route = [
            'method' => $method,
            'path_template' => $path,
            'path' => '#^' . $pattern . '$#',
            'callback' => $callback,
            'parameters' => $parameterNames,
            'middleware' => $middleware,
            'priority' => (int)($options['priority'] ?? $group['priority'] ?? 0),
            'plugin_id' => $options['plugin_id'] ?? null,
            'sequence' => $this->sequence++,
            'specificity' => substr_count($path, '/') * 1000 - count($parameterNames),
        ];
        $this->routes[] = $route;
    }

    public function get(string $path, mixed $callback, array $options = []): void { $this->add('GET', $path, $callback, $options); }
    public function post(string $path, mixed $callback, array $options = []): void { $this->add('POST', $path, $callback, $options); }
    public function put(string $path, mixed $callback, array $options = []): void { $this->add('PUT', $path, $callback, $options); }
    public function patch(string $path, mixed $callback, array $options = []): void { $this->add('PATCH', $path, $callback, $options); }
    public function delete(string $path, mixed $callback, array $options = []): void { $this->add('DELETE', $path, $callback, $options); }

    public function group(array $options, callable $registrar): void {
        $this->groupStack[] = $options;
        try { $registrar($this); } finally { array_pop($this->groupStack); }
    }

    public function url(string $name, array $parameters = []): string {
        if (!isset($this->namedRoutes[$name])) throw new \RuntimeException("命名路由不存在：{$name}");
        $path = $this->namedRoutes[$name];
        foreach ($parameters as $key => $value) {
            $path = str_replace([':' . $key, '{' . $key . '}'], rawurlencode((string)$value), $path);
        }
        if (preg_match('/:[a-zA-Z_]|\{[a-zA-Z_]/', $path)) throw new \InvalidArgumentException('命名路由缺少参数');
        return url($path);
    }

    public function run(): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        $basePath = defined('APP_BASE_PATH') ? (string)APP_BASE_PATH : '';
        if ($basePath !== '' && str_starts_with($uri, $basePath)) $uri = substr($uri, strlen($basePath)) ?: '/';
        if (($uri === '/index.php' || $uri === '') && isset($_GET['r'])) $uri = '/' . ltrim((string)$_GET['r'], '/');
        if ($uri === '') $uri = '/';

        $routes = $this->routes;
        usort($routes, static fn(array $a, array $b): int => [$a['priority'], -$a['specificity'], $a['sequence']] <=> [$b['priority'], -$b['specificity'], $b['sequence']]);
        foreach ($routes as $route) {
            if ($route['method'] !== $method || !preg_match($route['path'], $uri, $matches)) continue;
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            if (!$this->runMiddleware($route['middleware'], $route)) return;
            $result = $this->invoke($route['callback'], $params);
            if ($result instanceof Response) $result->send();
            elseif (is_string($result)) echo $result;
            elseif (is_array($result)) Response::json($result)->send();
            return;
        }
        if (class_exists(PluginRuntime::class)) PluginRuntime::instance()->events()->dispatch('system.404', ['path' => $uri, 'method' => $method]);
        (new SiteController())->notFound();
    }

    private function invoke(mixed $callback, array $params): mixed {
        if (is_array($callback)) {
            [$class, $action] = $callback;
            $controller = new $class();
            return $controller->$action(...$params);
        }
        if (is_callable($callback)) return $callback(...$params);
        if (is_string($callback)) return Handler::invoke($callback, [Request::capture(), ...$params]);
        throw new \RuntimeException('路由处理器不可调用');
    }

    private function runMiddleware(array $middleware, array $route): bool {
        foreach ($middleware as $item) {
            if ($item === 'admin') {
                if (!AuthManager::isAuthenticated()) { header('Location: ' . url('/admin/login')); return false; }
                continue;
            }
            if (is_string($item) && str_starts_with($item, 'permission:')) {
                $permission = substr($item, strlen('permission:'));
                if (!AuthManager::hasPermission($permission)) { http_response_code(403); echo 'Forbidden'; return false; }
                continue;
            }
            if ($item === 'csrf' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') { csrf_check(); continue; }
            if ($item === 'member') {
                $services = PluginRuntime::instance()->services();
                if (!$services->has('member.identity') || !$services->get('member.identity')->current()) {
                    header('Location: ' . url('/login')); return false;
                }
                continue;
            }
            if ($item === 'webhook') continue;
            if (is_callable($item)) {
                $result = $item(Request::capture(), $route);
                if ($result instanceof Response) { $result->send(); return false; }
                if ($result === false) return false;
                continue;
            }
            if (is_string($item) && str_contains($item, '@')) {
                try {
                    if ($route['plugin_id']) PluginRuntime::beginInvocation((string)$route['plugin_id']);
                    $result = Handler::invoke($item, [Request::capture(), $route]);
                    if ($result instanceof Response) { $result->send(); return false; }
                    if ($result === false) return false;
                } catch (\Throwable $e) {
                    if ($route['plugin_id']) PluginRuntime::instance()->registry()->recordFailure((string)$route['plugin_id'], $e);
                    http_response_code(500);
                    echo defined('APP_DEBUG') && APP_DEBUG ? h($e->getMessage()) : 'Plugin middleware failed';
                    return false;
                } finally {
                    if ($route['plugin_id']) PluginRuntime::endInvocation();
                }
            }
        }
        return true;
    }

    private function mergedGroup(): array {
        $merged = ['prefix' => '', 'middleware' => []];
        foreach ($this->groupStack as $group) {
            $merged['prefix'] = rtrim($merged['prefix'], '/') . '/' . ltrim((string)($group['prefix'] ?? ''), '/');
            $merged['middleware'] = array_merge($merged['middleware'], (array)($group['middleware'] ?? []));
            if (isset($group['priority'])) $merged['priority'] = $group['priority'];
        }
        $merged['prefix'] = rtrim($merged['prefix'], '/');
        return $merged;
    }
}
