<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;
use SQLite3Result;

/**
 * 媒体文件服务
 *
 * 负责 uploads 目录管理，以及 media_files 表的索引维护。
 */
class MediaManager
{
    private const IMAGE_MIME_MAP = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/x-icon' => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'image/bmp' => 'bmp',
    ];

    private const VIDEO_MIME_MAP = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
    private const MAX_VIDEO_SIZE = 50 * 1024 * 1024;

    private string $rootDir;
    private SQLite3 $db;
    private bool $indexSynchronized = false;

    public function __construct()
    {
        $this->rootDir = APP_ROOT . '/uploads';
        $this->ensureRootDirectory();
        $this->db = Database::getInstance();
        $this->ensureMediaTable();
    }

    public function getDefaultUploadDirectory(): string
    {
        return date('Ym');
    }

    public function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory));
        $directory = trim($directory, '/');

        if ($directory === '' || $directory === '.') {
            return '';
        }

        if (str_starts_with($directory, 'uploads/')) {
            $directory = substr($directory, strlen('uploads/'));
        } elseif ($directory === 'uploads') {
            return '';
        }

        $segments = preg_split('#/+#', $directory) ?: [];
        $normalized = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..' || str_starts_with($segment, '.')) {
                throw new \RuntimeException('无效的目录路径');
            }

            if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s._-]{0,99}$/u', $segment)) {
                throw new \RuntimeException('目录名称包含非法字符');
            }

            $normalized[] = $segment;
        }

        return implode('/', $normalized);
    }

    public function summarize(): array
    {
        $this->synchronizeIndex();

        $summary = [
            'total_size' => (int)$this->db->querySingle('SELECT COALESCE(SUM(size), 0) FROM media_files'),
            'total_count' => (int)$this->db->querySingle('SELECT COUNT(*) FROM media_files'),
            'image_count' => (int)$this->db->querySingle("SELECT COUNT(*) FROM media_files WHERE media_type = 'image'"),
            'video_count' => (int)$this->db->querySingle("SELECT COUNT(*) FROM media_files WHERE media_type = 'video'"),
            'folder_count' => $this->countFolders(),
        ];

        $summary['total_size_formatted'] = $this->formatBytes($summary['total_size']);

        return $summary;
    }

    public function listDirectory(string $directory = '', string $search = '', string $type = 'all', string $sort = 'date_desc'): array
    {
        $this->synchronizeIndex();

        $directory = $this->normalizeDirectory($directory);
        $absoluteDirectory = $this->absoluteDirectory($directory, false);

        if (!is_dir($absoluteDirectory)) {
            throw new \RuntimeException('目录不存在');
        }

        return [
            'directory' => $directory,
            'directory_public_path' => $this->directoryPublicPath($directory),
            'directory_display' => $directory === '' ? '/uploads' : '/uploads/' . $directory,
            'breadcrumbs' => $this->buildBreadcrumbs($directory),
            'folders' => $folders = $this->listFolders($absoluteDirectory, $directory, $search),
            'files' => $files = $this->queryFiles($directory, $search, $type, false, $sort),
            'folder_tree' => $this->buildFolderTree('', $directory),
            'current_stats' => [
                'folder_count' => count($folders),
                'file_count' => count($files),
                'item_count' => count($folders) + count($files),
                'total_size' => array_sum(array_map(static fn(array $file): int => (int)($file['size'] ?? 0), $files)),
                'total_size_formatted' => $this->formatBytes(array_sum(array_map(static fn(array $file): int => (int)($file['size'] ?? 0), $files))),
            ],
        ];
    }

    public function getLibraryPayload(string $directory = '', string $search = '', string $type = 'all', string $sort = 'date_desc'): array
    {
        $listing = $this->listDirectory($directory, $search, $type, $sort);
        if ($directory === '' && trim($search) !== '') {
            $listing['files'] = $this->queryFiles('', $search, $type, true, $sort);
        }

        return [
            'current_directory' => $listing['directory'],
            'current_directory_label' => $listing['directory_display'],
            'breadcrumbs' => $listing['breadcrumbs'],
            'folders' => $listing['folders'],
            'files' => $listing['files'],
            'folder_tree' => $listing['folder_tree'],
            'current_stats' => $listing['current_stats'],
            'filters' => [
                'search' => $search,
                'type' => $this->normalizeMediaTypeFilter($type),
                'sort' => $this->normalizeSortOption($sort),
            ],
            'summary' => $this->summarize(),
        ];
    }

    public function listImageLibraryPaths(): array
    {
        $this->synchronizeIndex();
        $result = $this->db->query("SELECT public_path FROM media_files WHERE media_type = 'image' ORDER BY created_at DESC, id DESC");
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = (string)$row['public_path'];
        }

        return $items;
    }

    public function uploadFiles(array $files, ?string $directory = null, bool $imagesOnly = false): array
    {
        $directory = $directory === null ? $this->getDefaultUploadDirectory() : $this->normalizeDirectory($directory);
        $absoluteDirectory = $this->absoluteDirectory($directory, true);

        $uploaded = [];
        $messages = [];

        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            [$ok, $result] = $this->storeUploadedFile($file, $absoluteDirectory, $directory, $imagesOnly);
            if ($ok) {
                $uploaded[] = $result;
            } else {
                $messages[] = $result;
            }
        }

        if ($uploaded === []) {
            throw new \RuntimeException($messages[0] ?? '上传失败');
        }

        $this->indexSynchronized = false;

        return [
            'uploaded' => $uploaded,
            'messages' => $messages,
            'directory' => $directory,
        ];
    }

    public function createFolder(string $parentDirectory, string $folderName): array
    {
        $parentDirectory = $this->normalizeDirectory($parentDirectory);
        $folderName = trim($folderName);

        if ($folderName === '') {
            throw new \RuntimeException('文件夹名称不能为空');
        }

        if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s._-]{0,99}$/u', $folderName) || str_starts_with($folderName, '.')) {
            throw new \RuntimeException('文件夹名称包含非法字符');
        }

        $targetDirectory = $parentDirectory === '' ? $folderName : $parentDirectory . '/' . $folderName;
        $absoluteTarget = $this->absoluteDirectory($targetDirectory, false);

        if (is_dir($absoluteTarget)) {
            throw new \RuntimeException('文件夹已存在');
        }

        if (!mkdir($absoluteTarget, 0755, true) && !is_dir($absoluteTarget)) {
            throw new \RuntimeException('文件夹创建失败');
        }

        clearstatcache(true, $absoluteTarget);

        return [
            'directory' => $targetDirectory,
            'public_path' => $this->directoryPublicPath($targetDirectory),
        ];
    }

    public function deleteFile(string $path): void
    {
        $absolutePath = $this->absoluteFilePath($path);
        if (!is_file($absolutePath)) {
            throw new \RuntimeException('文件不存在');
        }

        $publicPath = $this->absolutePathToPublicPath($absolutePath);
        if (!unlink($absolutePath)) {
            throw new \RuntimeException('删除文件失败');
        }

        $stmt = $this->db->prepare('DELETE FROM media_files WHERE public_path = :public_path');
        $stmt->bindValue(':public_path', $publicPath, SQLITE3_TEXT);
        $stmt->execute();

        clearstatcache(true, $absolutePath);
        $this->indexSynchronized = false;
    }

    public function deleteFiles(array $paths): array
    {
        $deleted = 0;
        $errors = [];

        foreach ($paths as $path) {
            $path = trim((string)$path);
            if ($path === '') {
                continue;
            }

            try {
                $this->deleteFile($path);
                $deleted++;
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($deleted === 0) {
            throw new \RuntimeException($errors[0] ?? '未选择可删除的文件');
        }

        return [
            'deleted_count' => $deleted,
            'errors' => array_values(array_unique(array_filter($errors))),
        ];
    }

    public function deleteFolder(string $directory): void
    {
        $directory = $this->normalizeDirectory($directory);
        if ($directory === '') {
            throw new \RuntimeException('不能删除根目录');
        }

        $absoluteDirectory = $this->absoluteDirectory($directory, false);
        if (!is_dir($absoluteDirectory)) {
            throw new \RuntimeException('文件夹不存在');
        }

        $items = scandir($absoluteDirectory);
        if ($items === false) {
            throw new \RuntimeException('无法读取文件夹');
        }

        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && !$this->shouldIgnoreItem($item)) {
                throw new \RuntimeException('请先清空文件夹后再删除');
            }
        }

        if (!rmdir($absoluteDirectory)) {
            throw new \RuntimeException('删除文件夹失败');
        }
    }

    public function connectorResponse(string $directory, string $mode = 'files', bool $onlyImages = false): array
    {
        $listing = $this->listDirectory($directory, '', $onlyImages ? 'image' : 'all', 'date_desc');
        $sourcePath = $listing['directory'] === '' ? '/' : '/' . trim($listing['directory'], '/') . '/';

        if ($mode === 'folders') {
            $folders = array_map(
                static fn(array $folder): string => $folder['name'],
                $listing['folders']
            );

            if ($listing['directory'] !== '') {
                array_unshift($folders, '..');
            }

            return $this->successResponse([
                'sources' => [[
                    'name' => 'default',
                    'baseurl' => rtrim(\base_url(), '/') . '/uploads/',
                    'path' => $sourcePath,
                    'folders' => $folders,
                ]],
                'code' => 220,
                'messages' => [],
            ]);
        }

        $items = [];
        foreach ($listing['files'] as $file) {
            $items[] = [
                'name' => $file['storage_name'],
                'file' => $file['storage_name'],
                'thumb' => $file['is_image'] ? $file['storage_name'] : null,
                'thumbIsAbsolute' => false,
                'type' => $file['type'],
                'changed' => date('c', $file['mtime']),
                'size' => $file['size_formatted'],
                'isImage' => $file['is_image'],
                'isVideo' => $file['is_video'],
            ];
        }

        return $this->successResponse([
            'sources' => [[
                'name' => 'default',
                'baseurl' => rtrim(\base_url(), '/') . '/uploads/',
                'path' => $sourcePath,
                'files' => $items,
            ]],
            'code' => 220,
            'messages' => [],
        ]);
    }

    public function permissionsResponse(): array
    {
        return $this->successResponse([
            'permissions' => [
                'allowFiles' => true,
                'allowFolders' => true,
                'allowFolderTree' => true,
                'allowFileUpload' => true,
                'allowFileUploadRemote' => false,
                'allowFileRemove' => true,
                'allowFileMove' => false,
                'allowFileRename' => false,
                'allowFolderCreate' => true,
                'allowFolderRemove' => true,
                'allowFolderMove' => false,
                'allowFolderRename' => false,
                'allowImageResize' => false,
                'allowImageCrop' => false,
            ],
            'messages' => [],
        ]);
    }

    public function uploadConnectorResponse(array $uploadResult): array
    {
        $first = $uploadResult['uploaded'][0];
        $directory = $uploadResult['directory'];

        return $this->successResponse([
            'baseurl' => rtrim(\base_url(), '/') . '/uploads/' . ($directory !== '' ? trim($directory, '/') . '/' : ''),
            'path' => $directory === '' ? '/' : '/' . trim($directory, '/') . '/',
            'files' => array_map(static fn(array $item): string => $item['storage_name'], $uploadResult['uploaded']),
            'newfilename' => $first['storage_name'],
            'messages' => empty($uploadResult['messages']) ? ['上传成功'] : $uploadResult['messages'],
        ]);
    }

    public function getPublicPathFromUrl(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $base = \base_path();
        if ($base !== '' && str_starts_with($path, $base . '/')) {
            $path = substr($path, strlen($base));
        }

        if (!str_starts_with($path, '/uploads/')) {
            return null;
        }

        $absolutePath = $this->absoluteFilePath($path);
        if (!is_file($absolutePath)) {
            return null;
        }

        $relativePath = ltrim(substr($path, strlen('/uploads/')), '/');
        $directory = dirname($relativePath);
        if ($directory === '.') {
            $directory = '';
        }

        return [
            'name' => basename($relativePath),
            'path' => $directory === '' ? '/' : '/' . $directory . '/',
            'source' => 'default',
        ];
    }

    public function successResponse(array $data = [], array $messages = []): array
    {
        if ($messages !== []) {
            $data['messages'] = $messages;
        }

        return [
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
            'data' => $data,
        ];
    }

    public function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'time' => date('Y-m-d H:i:s'),
            'data' => [
                'messages' => [$message],
            ],
        ];
    }

    private function ensureRootDirectory(): void
    {
        if (!is_dir($this->rootDir) && !mkdir($concurrentDirectory = $this->rootDir, 0755, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException('无法创建 uploads 目录');
        }

        $indexFile = $this->rootDir . '/index.html';
        if (!is_file($indexFile)) {
            file_put_contents($indexFile, '');
        }

        $htaccessFile = $this->rootDir . '/.htaccess';
        $htaccessContent = implode("\n", [
            'Options -Indexes',
            '<FilesMatch "\.(php|phtml|phar|cgi|pl|py|sh)$">',
            '    Require all denied',
            '</FilesMatch>',
            'RemoveHandler .php .phtml .phar',
            '',
        ]);

        if (!is_file($htaccessFile) || trim((string)file_get_contents($htaccessFile)) !== trim($htaccessContent)) {
            file_put_contents($htaccessFile, $htaccessContent);
        }
    }

    private function ensureMediaTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS media_files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                original_name TEXT NOT NULL,
                storage_name TEXT NOT NULL,
                title TEXT,
                alt_text TEXT,
                directory TEXT NOT NULL DEFAULT '',
                public_path TEXT NOT NULL UNIQUE,
                media_type TEXT NOT NULL DEFAULT 'image',
                mime_type TEXT,
                extension TEXT,
                size INTEGER NOT NULL DEFAULT 0,
                width INTEGER NOT NULL DEFAULT 0,
                height INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_media_files_directory ON media_files(directory);
            CREATE INDEX IF NOT EXISTS idx_media_files_media_type ON media_files(media_type);
            CREATE INDEX IF NOT EXISTS idx_media_files_created_at ON media_files(created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_media_files_original_name ON media_files(original_name);
        ");
    }

    private function synchronizeIndex(): void
    {
        if ($this->indexSynchronized) {
            return;
        }

        $this->indexSynchronized = true;

        $existing = [];
        $result = $this->db->query('SELECT * FROM media_files');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $existing[$row['public_path']] = $row;
        }

        $seen = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->rootDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $filename = $item->getFilename();
            if ($this->shouldIgnoreItem($filename)) {
                continue;
            }

            $absolutePath = $item->getPathname();
            $publicPath = $this->absolutePathToPublicPath($absolutePath);
            $seen[$publicPath] = true;
            $directory = $this->directoryFromAbsolutePath($absolutePath);

            $this->upsertMediaRecord($this->buildMediaMetadata(
                $absolutePath,
                $directory,
                $existing[$publicPath] ?? []
            ));
        }

        foreach (array_keys($existing) as $publicPath) {
            if (!isset($seen[$publicPath])) {
                $stmt = $this->db->prepare('DELETE FROM media_files WHERE public_path = :public_path');
                $stmt->bindValue(':public_path', $publicPath, SQLITE3_TEXT);
                $stmt->execute();
            }
        }
    }

    private function absoluteDirectory(string $directory, bool $create = false): string
    {
        $absolute = $this->rootDir . ($directory === '' ? '' : '/' . $directory);

        if ($create && !is_dir($absolute) && !mkdir($absolute, 0755, true) && !is_dir($absolute)) {
            throw new \RuntimeException('创建目录失败');
        }

        $realBase = realpath($this->rootDir);
        $realTarget = realpath($absolute);

        if ($realBase === false) {
            throw new \RuntimeException('媒体目录不可用');
        }

        if ($realTarget === false) {
            if (!$create) {
                return $absolute;
            }

            $realTarget = realpath($absolute);
        }

        if ($realTarget === false || !str_starts_with($realTarget, $realBase)) {
            throw new \RuntimeException('无效的目录路径');
        }

        return $realTarget;
    }

    private function absoluteFilePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if (str_starts_with($path, '/uploads/')) {
            $path = substr($path, strlen('/uploads/'));
        }

        $directory = dirname($path);
        $filename = basename($path);

        if ($filename === '' || str_starts_with($filename, '.')) {
            throw new \RuntimeException('无效的文件路径');
        }

        $directory = $directory === '.' ? '' : $this->normalizeDirectory($directory);
        $absoluteDirectory = $this->absoluteDirectory($directory, false);
        $absolutePath = $absoluteDirectory . '/' . $filename;

        $realBase = realpath($this->rootDir);
        $realPath = realpath($absolutePath);

        if ($realBase === false || $realPath === false || !str_starts_with($realPath, $realBase)) {
            throw new \RuntimeException('无效的文件路径');
        }

        return $realPath;
    }

    private function storeUploadedFile(array $file, string $absoluteDirectory, string $directory, bool $imagesOnly): array
    {
        if (!empty($file['error'])) {
            return [false, '上传失败'];
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return [false, '非法上传'];
        }

        $detected = $this->detectUploadType($file);
        if ($detected === null) {
            return [false, '仅支持 JPG、PNG、GIF、WebP、ICO、BMP、MP4、WebM、OGV、MOV 文件'];
        }

        if ($imagesOnly && $detected['type'] !== 'image') {
            return [false, '仅允许上传图片'];
        }

        $maxSize = $detected['type'] === 'video' ? self::MAX_VIDEO_SIZE : self::MAX_IMAGE_SIZE;
        if ((int)($file['size'] ?? 0) > $maxSize) {
            return [false, $detected['type'] === 'video' ? '视频过大，请小于 50MB' : '图片过大，请小于 10MB'];
        }

        $originalName = $this->sanitizeOriginalFilename((string)($file['name'] ?? ''));
        $filename = $this->generateStoredFilename($originalName, $detected['type'], $detected['extension']);
        $targetPath = $absoluteDirectory . '/' . $filename;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            return [false, '保存失败'];
        }

        clearstatcache(true, $targetPath);
        $metadata = $this->buildMediaMetadata($targetPath, $directory, [
            'original_name' => $originalName,
            'storage_name' => $filename,
            'media_type' => $detected['type'],
            'mime_type' => $detected['mime'],
            'extension' => $detected['extension'],
            'created_at' => gmdate('c'),
        ]);
        $this->upsertMediaRecord($metadata);

        return [true, $this->hydrateMediaRecord($metadata)];
    }

    private function detectUploadType(array $file): ?array
    {
        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_file($tmpName)) {
            return null;
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo !== false) {
            $mime = (string)($imageInfo['mime'] ?? '');
            if (isset(self::IMAGE_MIME_MAP[$mime])) {
                return [
                    'type' => 'image',
                    'extension' => self::IMAGE_MIME_MAP[$mime],
                    'mime' => $mime,
                ];
            }
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string)$finfo->file($tmpName);
        if (isset(self::VIDEO_MIME_MAP[$mime])) {
            return [
                'type' => 'video',
                'extension' => self::VIDEO_MIME_MAP[$mime],
                'mime' => $mime,
            ];
        }

        return null;
    }

    private function buildMediaMetadata(string $absolutePath, string $directory, array $existing = []): array
    {
        $storageName = basename($absolutePath);
        $publicPath = $this->absolutePathToPublicPath($absolutePath);
        $size = filesize($absolutePath) ?: 0;
        $mtime = filemtime($absolutePath) ?: time();
        $type = (string)($existing['media_type'] ?? $this->detectFileTypeByName($storageName));
        $mimeType = (string)($existing['mime_type'] ?? $this->detectMimeType($absolutePath));
        $extension = (string)($existing['extension'] ?? strtolower(pathinfo($storageName, PATHINFO_EXTENSION)));
        $originalName = $this->sanitizeOriginalFilename((string)($existing['original_name'] ?? $storageName));
        $title = trim((string)($existing['title'] ?? pathinfo($originalName, PATHINFO_FILENAME)));
        $createdAt = (string)($existing['created_at'] ?? gmdate('c', $mtime));

        $width = 0;
        $height = 0;
        if ($type === 'image') {
            $info = @getimagesize($absolutePath);
            if ($info !== false) {
                $width = (int)($info[0] ?? 0);
                $height = (int)($info[1] ?? 0);
                if ($mimeType === '') {
                    $mimeType = (string)($info['mime'] ?? '');
                }
            }
        }

        return [
            'original_name' => $originalName,
            'storage_name' => $storageName,
            'title' => $title,
            'alt_text' => (string)($existing['alt_text'] ?? ''),
            'directory' => $directory,
            'public_path' => $publicPath,
            'media_type' => $type,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'created_at' => $createdAt,
            'updated_at' => gmdate('c', $mtime),
        ];
    }

    private function upsertMediaRecord(array $metadata): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO media_files (
                original_name, storage_name, title, alt_text, directory, public_path,
                media_type, mime_type, extension, size, width, height, created_at, updated_at
            ) VALUES (
                :original_name, :storage_name, :title, :alt_text, :directory, :public_path,
                :media_type, :mime_type, :extension, :size, :width, :height, :created_at, :updated_at
            )
            ON CONFLICT(public_path) DO UPDATE SET
                original_name = excluded.original_name,
                storage_name = excluded.storage_name,
                title = excluded.title,
                alt_text = excluded.alt_text,
                directory = excluded.directory,
                media_type = excluded.media_type,
                mime_type = excluded.mime_type,
                extension = excluded.extension,
                size = excluded.size,
                width = excluded.width,
                height = excluded.height,
                updated_at = excluded.updated_at'
        );

        $stmt->bindValue(':original_name', $metadata['original_name'], SQLITE3_TEXT);
        $stmt->bindValue(':storage_name', $metadata['storage_name'], SQLITE3_TEXT);
        $stmt->bindValue(':title', $metadata['title'], SQLITE3_TEXT);
        $stmt->bindValue(':alt_text', $metadata['alt_text'], SQLITE3_TEXT);
        $stmt->bindValue(':directory', $metadata['directory'], SQLITE3_TEXT);
        $stmt->bindValue(':public_path', $metadata['public_path'], SQLITE3_TEXT);
        $stmt->bindValue(':media_type', $metadata['media_type'], SQLITE3_TEXT);
        $stmt->bindValue(':mime_type', $metadata['mime_type'], SQLITE3_TEXT);
        $stmt->bindValue(':extension', $metadata['extension'], SQLITE3_TEXT);
        $stmt->bindValue(':size', (int)$metadata['size'], SQLITE3_INTEGER);
        $stmt->bindValue(':width', (int)$metadata['width'], SQLITE3_INTEGER);
        $stmt->bindValue(':height', (int)$metadata['height'], SQLITE3_INTEGER);
        $stmt->bindValue(':created_at', $metadata['created_at'], SQLITE3_TEXT);
        $stmt->bindValue(':updated_at', $metadata['updated_at'], SQLITE3_TEXT);
        $stmt->execute();
    }

    private function listFolders(string $absoluteDirectory, string $directory, string $search = ''): array
    {
        clearstatcache(true, $absoluteDirectory);
        $items = scandir($absoluteDirectory);
        if ($items === false) {
            throw new \RuntimeException('无法读取目录');
        }

        $search = trim(mb_strtolower($search));
        $folders = [];

        foreach ($items as $itemName) {
            if ($itemName === '.' || $itemName === '..' || $this->shouldIgnoreItem($itemName)) {
                continue;
            }

            $absolutePath = $absoluteDirectory . '/' . $itemName;
            if (!is_dir($absolutePath)) {
                continue;
            }

            if ($search !== '' && !str_contains(mb_strtolower($itemName), $search)) {
                continue;
            }

            $childDirectory = $directory === '' ? $itemName : $directory . '/' . $itemName;
            $folders[] = [
                'name' => $itemName,
                'directory' => $childDirectory,
                'public_path' => $this->directoryPublicPath($childDirectory),
                'modified_at' => filemtime($absolutePath) ?: time(),
                'item_count' => $this->countDirectoryItems($absolutePath),
            ];
        }

        usort($folders, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

        return $folders;
    }

    private function buildFolderTree(string $directory = '', string $currentDirectory = ''): array
    {
        $absoluteDirectory = $this->absoluteDirectory($directory, false);
        $items = scandir($absoluteDirectory);
        if ($items === false) {
            return [];
        }

        $nodes = [];
        foreach ($items as $itemName) {
            if ($itemName === '.' || $itemName === '..' || $this->shouldIgnoreItem($itemName)) {
                continue;
            }

            $absolutePath = $absoluteDirectory . '/' . $itemName;
            if (!is_dir($absolutePath)) {
                continue;
            }

            $childDirectory = $directory === '' ? $itemName : $directory . '/' . $itemName;
            $nodes[] = [
                'name' => $itemName,
                'directory' => $childDirectory,
                'is_current' => $childDirectory === $currentDirectory,
                'is_ancestor' => $currentDirectory !== '' && str_starts_with($currentDirectory . '/', $childDirectory . '/'),
                'children' => $this->buildFolderTree($childDirectory, $currentDirectory),
            ];
        }

        usort($nodes, static fn(array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

        return $nodes;
    }

    private function queryFiles(string $directory, string $search, string $type, bool $ignoreDirectoryWhenRootSearch = false, string $sort = 'date_desc'): array
    {
        $type = $this->normalizeMediaTypeFilter($type);
        $sort = $this->normalizeSortOption($sort);
        $search = trim($search);
        $searchAllDirectories = $ignoreDirectoryWhenRootSearch && $directory === '' && $search !== '';

        $sql = 'SELECT * FROM media_files WHERE 1 = 1';
        $bindings = [];

        if (!$searchAllDirectories) {
            $sql .= ' AND directory = :directory';
            $bindings[':directory'] = ['value' => $directory, 'type' => SQLITE3_TEXT];
        }

        if ($type !== 'all') {
            $sql .= ' AND media_type = :media_type';
            $bindings[':media_type'] = ['value' => $type, 'type' => SQLITE3_TEXT];
        }

        if ($search !== '') {
            $sql .= ' AND (
                lower(original_name) LIKE :search
                OR lower(storage_name) LIKE :search
                OR lower(title) LIKE :search
                OR lower(directory) LIKE :search
            )';
            $bindings[':search'] = ['value' => '%' . mb_strtolower($search) . '%', 'type' => SQLITE3_TEXT];
        }

        $sql .= ' ORDER BY ' . $this->sortSql($sort);
        $stmt = $this->db->prepare($sql);
        foreach ($bindings as $key => $binding) {
            $stmt->bindValue($key, $binding['value'], $binding['type']);
        }

        $result = $stmt->execute();
        return $this->hydrateRows($result);
    }

    private function hydrateRows(SQLite3Result|false $result): array
    {
        $rows = [];
        if ($result === false) {
            return $rows;
        }

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $this->hydrateMediaRecord($row);
        }

        return $rows;
    }

    private function hydrateMediaRecord(array $row): array
    {
        $mtime = strtotime((string)($row['updated_at'] ?? '')) ?: time();
        $type = (string)($row['media_type'] ?? 'file');
        $originalName = (string)($row['original_name'] ?? '');
        $storageName = (string)($row['storage_name'] ?? basename((string)($row['public_path'] ?? '')));
        $publicPath = (string)($row['public_path'] ?? '');
        $width = (int)($row['width'] ?? 0);
        $height = (int)($row['height'] ?? 0);
        $size = (int)($row['size'] ?? 0);

        return [
            'id' => isset($row['id']) ? (int)$row['id'] : 0,
            'name' => $originalName !== '' ? $originalName : $storageName,
            'title' => (string)($row['title'] ?? ''),
            'original_name' => $originalName,
            'storage_name' => $storageName,
            'directory' => (string)($row['directory'] ?? ''),
            'relative_path' => ltrim(((string)($row['directory'] ?? '') !== '' ? (string)$row['directory'] . '/' : '') . $storageName, '/'),
            'public_path' => $publicPath,
            'public_url' => \asset_url($publicPath),
            'type' => $type,
            'mime_type' => (string)($row['mime_type'] ?? ''),
            'extension' => (string)($row['extension'] ?? ''),
            'is_image' => $type === 'image',
            'is_video' => $type === 'video',
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'mtime' => $mtime,
            'date' => date('Y-m-d H:i', $mtime),
            'width' => $width,
            'height' => $height,
            'dimensions' => $width > 0 && $height > 0 ? $width . ' x ' . $height : '',
        ];
    }

    private function detectFileTypeByName(string $name): string
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'bmp'], true)) {
            return 'image';
        }

        if (in_array($extension, ['mp4', 'webm', 'ogv', 'mov'], true)) {
            return 'video';
        }

        return 'file';
    }

    private function detectMimeType(string $absolutePath): string
    {
        if (!is_file($absolutePath)) {
            return '';
        }

        $info = @getimagesize($absolutePath);
        if ($info !== false && !empty($info['mime'])) {
            return (string)$info['mime'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return (string)$finfo->file($absolutePath);
    }

    private function sanitizeOriginalFilename(string $name): string
    {
        $name = trim(str_replace(['\\', '/'], ' ', $name));
        $name = preg_replace('/\s+/', ' ', $name) ?: '';
        $name = trim($name);

        if ($name === '' || str_starts_with($name, '.')) {
            return 'media-file';
        }

        return mb_substr($name, 0, 180);
    }

    private function generateStoredFilename(string $originalName, string $type, string $extension): string
    {
        $prefix = $type === 'video' ? 'vid_' : 'img_';
        $random = bin2hex(random_bytes(6));

        return $prefix . date('YmdHis') . '_' . $random . '.' . strtolower($extension);
    }

    private function normalizeMediaTypeFilter(string $type): string
    {
        $type = strtolower(trim($type));
        return in_array($type, ['all', 'image', 'video', 'file'], true) ? $type : 'all';
    }

    private function normalizeSortOption(string $sort): string
    {
        $sort = strtolower(trim($sort));
        return in_array($sort, ['date_desc', 'date_asc', 'name_asc', 'name_desc', 'type_asc', 'type_desc'], true)
            ? $sort
            : 'date_desc';
    }

    private function sortSql(string $sort): string
    {
        return match ($sort) {
            'date_asc' => 'created_at ASC, id ASC',
            'name_asc' => 'lower(original_name) ASC, lower(storage_name) ASC, id DESC',
            'name_desc' => 'lower(original_name) DESC, lower(storage_name) DESC, id DESC',
            'type_asc' => 'media_type ASC, lower(original_name) ASC, id DESC',
            'type_desc' => 'media_type DESC, lower(original_name) ASC, id DESC',
            default => 'created_at DESC, id DESC',
        };
    }

    private function absolutePathToPublicPath(string $absolutePath): string
    {
        $normalizedAbsolute = str_replace('\\', '/', $absolutePath);
        $normalizedRoot = str_replace('\\', '/', $this->rootDir);

        return '/uploads' . substr($normalizedAbsolute, strlen($normalizedRoot));
    }

    private function directoryFromAbsolutePath(string $absolutePath): string
    {
        $publicPath = $this->absolutePathToPublicPath($absolutePath);
        $relativePath = ltrim(substr($publicPath, strlen('/uploads/')), '/');
        $directory = dirname($relativePath);

        return $directory === '.' ? '' : $directory;
    }

    private function directoryPublicPath(string $directory): string
    {
        return '/uploads' . ($directory === '' ? '' : '/' . trim($directory, '/'));
    }

    private function buildBreadcrumbs(string $directory): array
    {
        $breadcrumbs = [
            [
                'name' => 'uploads',
                'directory' => '',
            ],
        ];

        if ($directory === '') {
            return $breadcrumbs;
        }

        $parts = explode('/', $directory);
        $path = '';
        foreach ($parts as $part) {
            $path = $path === '' ? $part : $path . '/' . $part;
            $breadcrumbs[] = [
                'name' => $part,
                'directory' => $path,
            ];
        }

        return $breadcrumbs;
    }

    private function countDirectoryItems(string $absoluteDirectory): int
    {
        clearstatcache(true, $absoluteDirectory);

        $items = scandir($absoluteDirectory);
        if ($items === false) {
            return 0;
        }

        $count = 0;
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && !$this->shouldIgnoreItem($item)) {
                $count++;
            }
        }

        return $count;
    }

    private function countFolders(): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->rootDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir() && !$this->shouldIgnoreItem($item->getFilename())) {
                $count++;
            }
        }

        return $count;
    }

    private function shouldIgnoreItem(string $name): bool
    {
        if ($name === '' || str_starts_with($name, '.')) {
            return true;
        }

        return in_array(strtolower($name), ['index.html'], true);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int)floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
