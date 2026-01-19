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
        
        // Support index.php?r=/path
        if ($uri === '/index.php' && isset($_GET['r'])) {
            $uri = '/' . ltrim((string)$_GET['r'], '/');
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

