<?php
declare(strict_types=1);

namespace App\Core;

final class AdminPageRenderer {
    public static function render(string $title, string $content, bool $showNav = true): void {
        require APP_ROOT . '/app/views/admin/layout.php';
    }
}
