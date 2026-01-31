<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\SiteController;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, $callback): void {
        $path = preg_replace('/\\:([a-z]+)/', '(?P<$1>[a-z0-9\\-]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => "#^$path$#",
            'callback' => $callback
        ];
    }

    public function run(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // 二级目录：去掉 base path 再匹配路由
        $basePath = defined('APP_BASE_PATH') ? (string) APP_BASE_PATH : '';
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }
        
        // Support index.php?r=/path
        if (($uri === '/index.php' || $uri === '') && isset($_GET['r'])) {
            $uri = '/' . ltrim((string)$_GET['r'], '/');
        }
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                if (is_array($route['callback'])) {
                    [$class, $action] = $route['callback'];
                    $controller = new $class();
                    $controller->$action(...$params);
                } else {
                    ($route['callback'])(...$params);
                }
                return;
            }
        }

        // 404
        $controller = new SiteController();
        $controller->notFound();
    }
}

