<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}
if (!extension_loaded('zip') || !extension_loaded('sodium')) {
    fwrite(STDERR, "The zip and sodium extensions are required.\n");
    exit(1);
}

$version = ltrim((string)($argv[1] ?? ''), 'v');
$outputDirectory = (string)($argv[2] ?? (__DIR__ . '/../dist'));
if (preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/D', $version) !== 1) {
    fwrite(STDERR, "Usage: php scripts/build-release.php <version> [output-directory]\n");
    exit(1);
}

$seedEncoded = trim((string)getenv('SHOPAGG_RELEASE_SIGNING_KEY'));
$seed = base64_decode(strtr($seedEncoded, '-_', '+/') . str_repeat('=', (4 - strlen($seedEncoded) % 4) % 4), true);
if (!is_string($seed) || strlen($seed) !== SODIUM_CRYPTO_SIGN_SEEDBYTES) {
    fwrite(STDERR, "SHOPAGG_RELEASE_SIGNING_KEY must be a base64url Ed25519 seed.\n");
    exit(1);
}

$root = realpath(__DIR__ . '/..');
if ($root === false) exit(1);
if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0755, true)) {
    fwrite(STDERR, "Unable to create output directory.\n");
    exit(1);
}
$outputDirectory = realpath($outputDirectory) ?: $outputDirectory;
$fileName = 'shopagg-cms-v' . $version . '.zip';
$zipPath = $outputDirectory . '/' . $fileName;
if (is_file($zipPath) || is_file($outputDirectory . '/manifest.json')) {
    fwrite(STDERR, "Release artifacts already exist; published versions are immutable.\n");
    exit(1);
}

$excludedRoots = ['.git', '.github', '.idea', '.vscode', 'node_modules', 'uploads', 'storage', 'dist', 'coverage', 'backups', 'logs'];
$excludedFiles = ['.env', '.env.local', '.DS_Store'];
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
    fwrite(STDERR, "Unable to create ZIP.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->isLink()) continue;
    $path = $file->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $parts = explode('/', $relative);
    $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
    if (in_array($parts[0] ?? '', $excludedRoots, true)
        || in_array(basename($relative), $excludedFiles, true)
        || in_array($extension, ['sqlite', 'sqlite3', 'db', 'log', 'bak'], true)
        || str_ends_with($relative, '.zip')) {
        continue;
    }
    if (!$zip->addFile($path, $relative)) {
        $zip->close();
        @unlink($zipPath);
        fwrite(STDERR, "Unable to add {$relative}.\n");
        exit(1);
    }
}
$zip->close();

$size = filesize($zipPath);
$sha256 = hash_file('sha256', $zipPath);
if (!is_int($size) || $size <= 0 || !is_string($sha256)) {
    @unlink($zipPath);
    fwrite(STDERR, "Unable to hash release package.\n");
    exit(1);
}

$repository = (string)(getenv('GITHUB_REPOSITORY') ?: 'zhpelo/shopagg-b2b-website');
$tag = 'v' . $version;
$payload = [
    'product' => 'shopagg-cms',
    'version' => $version,
    'channel' => 'stable',
    'published_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'notes' => trim((string)getenv('RELEASE_NOTES')) ?: 'SHOPAGG CMS v' . $version,
    'compatibility' => [
        'php' => '>=8.1.0',
        'sqlite' => '>=3.35.0',
        'source_version' => '>=1.3.0',
    ],
    'artifact' => [
        'path' => '/downloads/' . $version . '/' . $fileName,
        'file_name' => $fileName,
        'size' => $size,
        'sha256' => $sha256,
    ],
    'github_fallback_url' => 'https://github.com/' . $repository . '/releases/download/' . $tag . '/' . $fileName,
    'force_update' => false,
];
$payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$signedPayload = rtrim(strtr(base64_encode($payloadJson), '+/', '-_'), '=');
$keypair = sodium_crypto_sign_seed_keypair($seed);
$signature = sodium_crypto_sign_detached($signedPayload, sodium_crypto_sign_secretkey($keypair));
$envelope = [
    'schema' => 1,
    'key_id' => 'shopagg-release-2026-01',
    'signed_payload' => $signedPayload,
    'signature' => rtrim(strtr(base64_encode($signature), '+/', '-_'), '='),
];
file_put_contents($outputDirectory . '/manifest.json', json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
file_put_contents($outputDirectory . '/SHA256SUMS', $sha256 . '  ' . $fileName . "\n", LOCK_EX);
fwrite(STDOUT, "Built {$fileName} ({$size} bytes)\n");
