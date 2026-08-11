<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Core\Router;
use App\Plugins\Http\Request;
use App\Plugins\Http\Response;
use App\Plugins\Support\Handler;

final class PluginRuntime {
    private static ?self $instance = null;
    private array $cache;
    private PluginRegistry $registry;
    private EventDispatcher $events;
    private FilterDispatcher $filters;
    private SlotRenderer $slots;
    private ServiceRegistry $services;
    private array $contexts = [];
    private ?Router $router = null;
    private static bool $schedulerRegistered = false;
    private static ?string $activePlugin = null;

    private function __construct() {
        $this->registry = new PluginRegistry();
        $this->consumeFatalMarkers();
        $cacheStore = new PluginCache();
        $this->cache = $cacheStore->load();
        $autoload = $this->cache['autoload'];
        spl_autoload_register(static function (string $class) use ($autoload): void {
            foreach ($autoload as $prefix => $directory) {
                if (!str_starts_with($class, $prefix)) continue;
                $file = rtrim($directory, '/') . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($file)) require $file;
                return;
            }
        }, true, true);
        foreach ($this->cache['plugins'] as $plugin) {
            $vendor = $plugin['base'] . '/vendor/autoload.php';
            if (is_file($vendor)) require_once $vendor;
        }
        $this->events = new EventDispatcher($this->cache['events'], $this->registry);
        $this->filters = new FilterDispatcher($this->cache['filters'], $this->registry);
        $this->slots = new SlotRenderer($this->cache['slots'], $this->registry);
        $this->services = new ServiceRegistry($this->cache['services']);
        if (!self::$schedulerRegistered && PHP_SAPI !== 'cli') {
            self::$schedulerRegistered = true;
            register_shutdown_function(static function (): void {
                $last = error_get_last();
                if ($last && in_array($last['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                    if (self::$activePlugin !== null) {
                        $directory = APP_ROOT . '/storage/plugin-failures';
                        if (!is_dir($directory)) @mkdir($directory, 0755, true);
                        @file_put_contents($directory . '/' . self::$activePlugin . '.json', json_encode($last, JSON_UNESCAPED_UNICODE), LOCK_EX);
                    }
                    return;
                }
                if (random_int(1, 100) !== 1) return;
                if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
                try { (new \App\Plugins\Jobs\JobRunner())->runDue(1); } catch (\Throwable $e) { error_log('[Plugin Scheduler] ' . $e->getMessage()); }
            });
        }
    }

    public static function boot(): self { return self::$instance ??= new self(); }
    public static function instance(): self { return self::boot(); }
    public static function reset(): void { self::$instance = null; }
    public static function beginInvocation(string $pluginId): void { self::$activePlugin = $pluginId; }
    public static function endInvocation(): void { self::$activePlugin = null; }
    public function registry(): PluginRegistry { return $this->registry; }
    public function cache(): array { return $this->cache; }
    public function events(): EventDispatcher { return $this->events; }
    public function filters(): FilterDispatcher { return $this->filters; }
    public function slots(): SlotRenderer { return $this->slots; }
    public function services(): ServiceRegistry { return $this->services; }
    public function router(): ?Router { return $this->router; }

    public function context(string $pluginId): PluginContext {
        if (isset($this->contexts[$pluginId])) return $this->contexts[$pluginId];
        $plugin = $this->cache['plugins'][$pluginId] ?? null;
        if ($plugin === null) {
            $row = $this->registry->find($pluginId);
            if (!$row) throw new \RuntimeException("插件不存在：{$pluginId}");
            $version = (string)($row['active_version'] ?: $row['installed_version']);
            $plugin = ['version' => $version, 'base' => APP_ROOT . '/storage/plugins/' . $pluginId . '/' . $version];
        }
        return $this->contexts[$pluginId] = new PluginContext($pluginId, $plugin['version'], $plugin['base'], $this);
    }

    public function registerRoutes(Router $router): void {
        $this->router = $router;
        foreach ($this->cache['routes'] as $route) {
            $pluginId = $route['plugin_id'];
            $handler = $route['handler'];
            $middleware = (array)($route['middleware'] ?? []);
            $router->add(
                strtoupper((string)($route['method'] ?? 'GET')),
                (string)$route['path'],
                function (...$params) use ($pluginId, $handler, $route): void {
                    try {
                        self::beginInvocation($pluginId);
                        $result = Handler::invoke($handler, [Request::capture(), $this->context($pluginId), ...$params]);
                        if ($result instanceof Response) { $result->send(); return; }
                        if (is_array($result)) { Response::json($result)->send(); return; }
                        $html = (string)($result ?? '');
                        if (($route['layout'] ?? 'standalone') === 'theme') {
                            (new \App\Controllers\PluginPageController())->show($html, (array)($route['seo'] ?? []));
                            return;
                        }
                        Response::html($html)->send();
                    } catch (\Throwable $e) {
                        $this->registry->recordFailure($pluginId, $e);
                        http_response_code(500);
                        echo APP_DEBUG ? h($e->getMessage()) : 'Plugin request failed';
                    } finally {
                        self::endInvocation();
                    }
                },
                ['name' => $route['name'] ?? null, 'priority' => (int)($route['priority'] ?? 50), 'middleware' => $middleware, 'plugin_id' => $pluginId]
            );
        }
    }

    public function renderPageSlots(string $html, array $context = []): string {
        $head = $this->assetTags('head', $context) . $this->slots->render('site.head', $context);
        $bodyStart = $this->slots->render('site.body_start', $context);
        $footer = $this->slots->render('site.footer', $context) . $this->assetTags('footer', $context);
        $before = $this->slots->render('site.content.before', $context);
        $after = $this->slots->render('site.content.after', $context);
        if ($head !== '') $html = str_ireplace('</head>', $head . '</head>', $html);
        if ($bodyStart !== '') $html = preg_replace('/<body([^>]*)>/i', '<body$1>' . $bodyStart, $html, 1) ?? $html;
        if ($footer !== '') $html = str_ireplace('</body>', $footer . '</body>', $html);
        if ($before !== '') $html = preg_replace('/<body([^>]*)>/i', '<body$1>' . $before, $html, 1) ?? $html;
        if ($after !== '') $html = str_ireplace('</body>', $after . '</body>', $html);
        return $html;
    }

    private function assetTags(string $location, array $context): string {
        $tags = '';
        $requestPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        foreach ($this->cache['assets'] as $asset) {
            $path = (string)($asset['path'] ?? $asset['value'] ?? '');
            if ($path === '' || !PluginManifest::safeRelativePath($path)) continue;
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $assetLocation = (string)($asset['location'] ?? ($extension === 'css' ? 'head' : 'footer'));
            if ($assetLocation !== $location) continue;
            $routes = (array)($asset['routes'] ?? []);
            if ($routes !== [] && !$this->matchesAnyPattern($requestPath, $routes)) continue;
            $views = (array)($asset['views'] ?? []);
            if ($views !== [] && !in_array((string)($context['view'] ?? ''), $views, true)) continue;
            $plugin = $this->cache['plugins'][$asset['plugin_id']] ?? null;
            if (!$plugin) continue;
            $url = (new PluginAssets($asset['plugin_id'], $plugin['version']))->url($path);
            if ($extension === 'css') {
                $tags .= '<link rel="stylesheet" href="' . h($url) . '">';
            } elseif (in_array($extension, ['js', 'mjs'], true)) {
                $type = $extension === 'mjs' || ($asset['module'] ?? false) ? ' type="module"' : '';
                $defer = ($asset['defer'] ?? true) ? ' defer' : '';
                $tags .= '<script src="' . h($url) . '"' . $type . $defer . '></script>';
            }
        }
        return $tags;
    }

    private function matchesAnyPattern(string $path, array $patterns): bool {
        foreach ($patterns as $pattern) {
            $quoted = preg_quote((string)$pattern, '#');
            if (preg_match('#^' . str_replace('\\*', '.*', $quoted) . '$#', $path)) return true;
        }
        return false;
    }

    public function rebuild(): void {
        (new PluginCache())->rebuild($this->registry->enabled());
        self::reset();
    }

    private function consumeFatalMarkers(): void {
        $directory = APP_ROOT . '/storage/plugin-failures';
        if (!is_dir($directory)) return;
        foreach (glob($directory . '/*.json') ?: [] as $file) {
            $pluginId = pathinfo($file, PATHINFO_FILENAME);
            $data = json_decode((string)file_get_contents($file), true) ?: [];
            @unlink($file);
            if ($this->registry->find($pluginId)) {
                $this->registry->recordFailure($pluginId, new \RuntimeException('Fatal plugin error: ' . ($data['message'] ?? 'unknown')));
            }
        }
    }
}
