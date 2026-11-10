<?php
declare(strict_types=1);

$manifestPath = (string)($argv[1] ?? '');
if ($manifestPath === '' || !is_file($manifestPath)) {
    fwrite(STDERR, "Usage: php scripts/verify-release.php <manifest.json>\n");
    exit(1);
}
require_once __DIR__ . '/../app/Services/Updates/ReleaseProvider.php';
require_once __DIR__ . '/../app/Services/Updates/UpdateSecurityException.php';
require_once __DIR__ . '/../app/Services/Updates/ShopaggUpdateProvider.php';

$envelope = json_decode((string)file_get_contents($manifestPath), true);
if (!is_array($envelope)) {
    fwrite(STDERR, "Manifest JSON is invalid.\n");
    exit(1);
}
try {
    $release = App\Services\Updates\ShopaggUpdateProvider::releaseFromEnvelope($envelope);
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
$zipPath = dirname($manifestPath) . '/' . basename((string)parse_url((string)$release['download_url'], PHP_URL_PATH));
if (!is_file($zipPath)
    || filesize($zipPath) !== (int)$release['size']
    || !hash_equals((string)$release['sha256'], (string)hash_file('sha256', $zipPath))) {
    fwrite(STDERR, "ZIP size or SHA-256 does not match the signed manifest.\n");
    exit(1);
}
fwrite(STDOUT, "Signed release verified: {$release['version']}\n");
