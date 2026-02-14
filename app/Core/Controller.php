<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller {
    protected function render(string $theme, string $view, array $data = []): void {
        $themePath = APP_ROOT . '/themes/' . $theme;
        $defaultThemePath = APP_ROOT . '/themes/default';
        
        if (!is_dir($themePath)) {
            $themePath = $defaultThemePath;
        }

        // 自动加载主题的 functions.php（优先当前主题，回退到默认主题）
        $functionsFile = $themePath . '/functions.php';
        if (!is_file($functionsFile)) {
            $functionsFile = $defaultThemePath . '/functions.php';
        }
        if (is_file($functionsFile)) {
            include_once $functionsFile;
        }

        extract($data, EXTR_SKIP);
        $currentTheme = $theme; // 将主题名称传递给视图

        // 加载视图文件（优先当前主题，回退到默认主题）
        $viewFile = $themePath . '/' . $view . '.php';
        if (!is_file($viewFile)) {
            $viewFile = $defaultThemePath . '/' . $view . '.php';
        }
        
        if (is_file($viewFile)) {
            ob_start();
            include $viewFile;
            $pageContent = ob_get_clean();
        } else {
            $pageContent = "<p>Missing view: $view</p>";
        }
        // 回退方案：加载 header 和 footer（优先当前主题，回退到默认主题）
        $headerFile = $themePath . '/header.php';
        if (!is_file($headerFile)) {
            $headerFile = $defaultThemePath . '/header.php';
        }
        
        $footerFile = $themePath . '/footer.php';
        if (!is_file($footerFile)) {
            $footerFile = $defaultThemePath . '/footer.php';
        }
        
        if (is_file($headerFile)) {
            include $headerFile;
        }
        echo $pageContent;
        if (is_file($footerFile)) {
            include $footerFile;
        }
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

