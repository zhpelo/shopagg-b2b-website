<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller {
    protected function render(string $theme, string $view, array $data = []): void {
        $themePath = APP_ROOT . '/themes/' . $theme;
        if (!is_dir($themePath)) {
            $themePath = APP_ROOT . '/themes/default';
        }

        // 自动加载主题的 functions.php
        $functionsFile = $themePath . '/functions.php';
        if (is_file($functionsFile)) {
            include_once $functionsFile;
        }

        extract($data, EXTR_SKIP);
        $currentTheme = $theme; // 将主题名称传递给视图
        include $themePath . '/header.php';
        $viewFile = $themePath . '/' . $view . '.php';
        if (is_file($viewFile)) {
            include $viewFile;
        } else {
            echo "<p>Missing view: $view</p>";
        }
        include $themePath . '/footer.php';
    }

    protected function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        // 相对路径（以 / 开头且非协议）在二级目录下需加上 base path
        if (strpos($url, 'http') !== 0 && strpos($url, '/') === 0 && function_exists('url')) {
            $url = url($url);
        }
        header("Location: $url");
        exit;
    }
}

