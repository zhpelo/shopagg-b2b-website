<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;

class Database {
    private static ?SQLite3 $instance = null;

    public static function getInstance(): SQLite3 {
        if (self::$instance === null) {
            $dbFile = APP_ROOT . '/#data/site.db';
            $isNew = !is_file($dbFile);
            if ($isNew) {
                @mkdir(APP_ROOT . '/#data/', 0755, true);
            }
            self::$instance = new SQLite3($dbFile);
            self::$instance->busyTimeout(5000);
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA journal_mode = WAL;');
            self::$instance->exec('PRAGMA busy_timeout = 5000;');
            if ($isNew) {
                self::initSchema(self::$instance);
            }
            self::ensureColumns(self::$instance);
        }
        return self::$instance;
    }

    /** 为表补充缺失列，避免重复 PRAGMA/ALTER 代码 */
    private static function addColumnsIfMissing(SQLite3 $db, string $table, array $columnDefs): void {
        $res = $db->query("PRAGMA table_info($table)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existing[] = $row['name'];
        }
        foreach ($columnDefs as $col => $type) {
            if (!in_array($col, $existing)) {
                $db->exec("ALTER TABLE $table ADD COLUMN $col $type");
            }
        }
    }

    private static function ensureColumns(SQLite3 $db): void {
        $db->exec("
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

        self::addColumnsIfMissing($db, 'products', [
            'status' => 'TEXT DEFAULT "active"',
            'product_type' => 'TEXT',
            'vendor' => 'TEXT',
            'tags' => 'TEXT',
            'images_json' => 'TEXT',
            'banner_image' => 'TEXT',
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ]);
        self::addColumnsIfMissing($db, 'inquiries', [
            'quantity' => 'TEXT',
            'status' => 'TEXT DEFAULT "pending"',
            'ip' => 'TEXT',
            'user_agent' => 'TEXT',
            'source_url' => 'TEXT',
        ]);
        self::addColumnsIfMissing($db, 'users', [
            'role' => 'TEXT DEFAULT "staff"',
            'permissions' => 'TEXT',
            'display_name' => 'TEXT',
        ]);
        $db->exec("UPDATE users SET role = 'admin' WHERE username = 'admin' AND (role IS NULL OR role != 'admin')");
        self::addColumnsIfMissing($db, 'product_categories', [
            'parent_id' => 'INTEGER DEFAULT 0',
            'type' => 'TEXT DEFAULT "product"',
            'sort_order' => 'INTEGER DEFAULT 0',
            'description' => 'TEXT',
        ]);
        self::addColumnsIfMissing($db, 'posts', [
            'post_type' => 'TEXT DEFAULT "post"',
            'category_id' => 'INTEGER DEFAULT 0',
            'status' => 'TEXT DEFAULT "active"',
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ]);
        $db->exec("UPDATE posts SET post_type = 'post' WHERE post_type IS NULL OR TRIM(post_type) = ''");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_posts_post_type ON posts(post_type)");
        self::migrateCasesToPosts($db);
    }

    private static function initSchema(SQLite3 $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                role TEXT DEFAULT 'staff',
                permissions TEXT,
                display_name TEXT,
                created_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                summary TEXT,
                content TEXT,
                category_id INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active',
                product_type TEXT,
                vendor TEXT,
                tags TEXT,
                images_json TEXT,
                seo_title TEXT,
                seo_keywords TEXT,
                seo_description TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS cases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                summary TEXT,
                content TEXT,
                cover TEXT,
                seo_title TEXT,
                seo_keywords TEXT,
                seo_description TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                post_type TEXT NOT NULL DEFAULT 'post',
                summary TEXT,
                content TEXT,
                cover TEXT,
                category_id INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active',
                seo_title TEXT,
                seo_keywords TEXT,
                seo_description TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS inquiries (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                company TEXT,
                phone TEXT,
                message TEXT,
                quantity TEXT,
                status TEXT DEFAULT 'pending',
                ip TEXT,
                user_agent TEXT,
                source_url TEXT,
                created_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                company TEXT,
                phone TEXT,
                message TEXT,
                created_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS product_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                parent_id INTEGER DEFAULT 0,
                type TEXT DEFAULT 'product',
                sort_order INTEGER DEFAULT 0,
                description TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            );
            CREATE TABLE IF NOT EXISTS product_prices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                min_qty INTEGER NOT NULL,
                max_qty INTEGER,
                price REAL NOT NULL,
                currency TEXT NOT NULL DEFAULT 'USD',
                created_at TEXT NOT NULL,
                FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
            );
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
        ");
        $db->exec("
            CREATE INDEX IF NOT EXISTS idx_media_files_directory ON media_files(directory);
            CREATE INDEX IF NOT EXISTS idx_media_files_media_type ON media_files(media_type);
            CREATE INDEX IF NOT EXISTS idx_media_files_created_at ON media_files(created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_media_files_original_name ON media_files(original_name);
        ");

        // Seed default admin
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, display_name, created_at) VALUES (:u, :p, :r, :d, :c)");
        $stmt->bindValue(':u', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':p', $passwordHash, SQLITE3_TEXT);
        $stmt->bindValue(':r', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':d', 'Administrator', SQLITE3_TEXT);
        $stmt->bindValue(':c', gmdate('c'), SQLITE3_TEXT);
        $stmt->execute();

        // Default settings
        $settings = [
            'site_name' => 'Global B2B Solutions',
            'site_tagline' => 'Trusted manufacturing partner for global buyers',
            'company_about' => 'We are a manufacturing and exporting company focused on quality, compliance, and fast delivery for global B2B clients.',
            'company_address' => 'No. 88, Industrial Park, Shenzhen, China',
            'company_email' => 'sales@example.com',
            'company_phone' => '+86-000-0000-0000',
            'theme' => 'default',
            'default_lang' => 'en',
            'whatsapp' => ''
        ];
        foreach ($settings as $k => $v) {
            $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (:k, :v)");
            $stmt->bindValue(':k', $k, SQLITE3_TEXT);
            $stmt->bindValue(':v', $v, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    private static function migrateCasesToPosts(SQLite3 $db): void {
        $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'cases'");
        if (!$tableExists) {
            return;
        }

        $result = $db->query("SELECT * FROM cases ORDER BY id ASC");
        if (!$result) {
            return;
        }

        while ($case = $result->fetchArray(SQLITE3_ASSOC)) {
            $existing = $db->prepare("SELECT id FROM posts WHERE post_type = :post_type AND slug = :slug LIMIT 1");
            $existing->bindValue(':post_type', 'case', SQLITE3_TEXT);
            $existing->bindValue(':slug', (string)($case['slug'] ?? ''), SQLITE3_TEXT);
            $existingRow = $existing->execute()?->fetchArray(SQLITE3_ASSOC);
            if ($existingRow) {
                continue;
            }

            $slug = self::resolveMigratedCaseSlug($db, (string)($case['slug'] ?? ''), (string)($case['title'] ?? ''), (int)($case['id'] ?? 0));
            $stmt = $db->prepare("
                INSERT INTO posts (
                    title, slug, post_type, summary, content, cover, category_id, status,
                    seo_title, seo_keywords, seo_description, created_at, updated_at
                ) VALUES (
                    :title, :slug, :post_type, :summary, :content, :cover, 0, 'active',
                    :seo_title, :seo_keywords, :seo_description, :created_at, :updated_at
                )
            ");
            $stmt->bindValue(':title', (string)($case['title'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
            $stmt->bindValue(':post_type', 'case', SQLITE3_TEXT);
            $stmt->bindValue(':summary', (string)($case['summary'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':content', (string)($case['content'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':cover', (string)($case['cover'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_title', (string)($case['seo_title'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_keywords', (string)($case['seo_keywords'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_description', (string)($case['seo_description'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':created_at', (string)($case['created_at'] ?? gmdate('c')), SQLITE3_TEXT);
            $stmt->bindValue(':updated_at', (string)($case['updated_at'] ?? gmdate('c')), SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    private static function resolveMigratedCaseSlug(SQLite3 $db, string $slug, string $title, int $caseId): string {
        $baseSlug = trim($slug);
        if ($baseSlug === '') {
            $baseSlug = self::slugifyFallback($title !== '' ? $title : 'case-' . $caseId);
        }

        $candidate = $baseSlug;
        $suffix = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM posts WHERE slug = :slug LIMIT 1");
            $stmt->bindValue(':slug', $candidate, SQLITE3_TEXT);
            $row = $stmt->execute()?->fetchArray(SQLITE3_ASSOC);
            if (!$row) {
                return $candidate;
            }
            $candidate = $baseSlug . '-case' . ($suffix > 1 ? '-' . $suffix : '');
            $suffix++;
        }
    }

    private static function slugifyFallback(string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');
        return $value !== '' ? $value : 'case';
    }
}
