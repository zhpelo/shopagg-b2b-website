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
                mkdir(APP_ROOT . '/#data/', 0755, true);
            }
            self::$instance = new SQLite3($dbFile);
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA journal_mode = WAL;');
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
        $db->exec("UPDATE users SET role = 'admin' WHERE username = 'admin'");
        self::addColumnsIfMissing($db, 'product_categories', [
            'parent_id' => 'INTEGER DEFAULT 0',
            'type' => 'TEXT DEFAULT "product"',
            'sort_order' => 'INTEGER DEFAULT 0',
            'description' => 'TEXT',
        ]);
        self::addColumnsIfMissing($db, 'posts', [
            'category_id' => 'INTEGER DEFAULT 0',
            'status' => 'TEXT DEFAULT "active"',
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ]);
        self::addColumnsIfMissing($db, 'cases', [
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ]);
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
                cover TEXT,
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
            CREATE TABLE IF NOT EXISTS product_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                url TEXT NOT NULL,
                sort INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
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
}

