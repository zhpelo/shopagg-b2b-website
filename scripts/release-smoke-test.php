<?php
declare(strict_types=1);

$root = realpath(__DIR__ . '/..');
$required = ['index.php', 'app/routes.php', 'app/Core/Database.php', 'app/Models/Updater.php'];
foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        fwrite(STDERR, "Missing required file: {$file}\n");
        exit(1);
    }
}
foreach (['curl', 'sqlite3', 'zip', 'sodium'] as $extension) {
    if (!extension_loaded($extension)) {
        fwrite(STDERR, "Missing PHP extension: {$extension}\n");
        exit(1);
    }
}
require_once $root . '/app/Services/Updates/ReleaseProvider.php';
require_once $root . '/app/Services/Updates/UpdateSecurityException.php';
require_once $root . '/app/Services/Updates/ShopaggUpdateProvider.php';
if (!class_exists(App\Services\Updates\ShopaggUpdateProvider::class)) exit(1);
fwrite(STDOUT, "Release smoke test passed.\n");
