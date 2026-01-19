<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller {
    protected function render(string $theme, string $view, array $data = []): void {
        $themePath = __DIR__ . '/../../themes/' . $theme;
        if (!is_dir($themePath)) {
            $themePath = __DIR__ . '/../../themes/default';
        }

        extract($data, EXTR_SKIP);
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
        header("Location: $url");
        exit;
    }
}

