<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;

class Database {
    private static ?SQLite3 $instance = null;

    public static function getInstance(): SQLite3 {
        if (self::$instance === null) {
            $dbFile = __DIR__ . '/../../#data/site.db';
            $isNew = !is_file($dbFile);
            if ($isNew) {
                mkdir(__DIR__ . '/../../#data/', 0755, true);
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

    private static function ensureColumns(SQLite3 $db): void {
        // Products columns
        $productCols = [
            'status' => 'TEXT DEFAULT "active"',
            'product_type' => 'TEXT',
            'vendor' => 'TEXT',
            'tags' => 'TEXT',
            'images_json' => 'TEXT'
        ];
        $res = $db->query("PRAGMA table_info(products)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $existing[] = $row['name']; }
        foreach ($productCols as $col => $type) {
            if (!in_array($col, $existing)) $db->exec("ALTER TABLE products ADD COLUMN $col $type");
        }

        // Inquiries columns
        $inquiryCols = [
            'quantity' => 'TEXT',
            'status' => 'TEXT DEFAULT "pending"',
            'ip' => 'TEXT',
            'user_agent' => 'TEXT',
            'source_url' => 'TEXT'
        ];
        $res = $db->query("PRAGMA table_info(inquiries)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $existing[] = $row['name']; }
        foreach ($inquiryCols as $col => $type) {
            if (!in_array($col, $existing)) $db->exec("ALTER TABLE inquiries ADD COLUMN $col $type");
        }

        // Users columns
        $userCols = [
            'role' => 'TEXT DEFAULT "staff"',
            'permissions' => 'TEXT',
            'display_name' => 'TEXT'
        ];
        $res = $db->query("PRAGMA table_info(users)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $existing[] = $row['name']; }
        foreach ($userCols as $col => $type) {
            if (!in_array($col, $existing)) $db->exec("ALTER TABLE users ADD COLUMN $col $type");
        }
        // Update default admin role
        $db->exec("UPDATE users SET role = 'admin' WHERE username = 'admin'");

        // Categories columns (支持多级分类和类型区分)
        $categoryCols = [
            'parent_id' => 'INTEGER DEFAULT 0',
            'type' => 'TEXT DEFAULT "product"',
            'sort_order' => 'INTEGER DEFAULT 0',
            'description' => 'TEXT'
        ];
        $res = $db->query("PRAGMA table_info(product_categories)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $existing[] = $row['name']; }
        foreach ($categoryCols as $col => $type) {
            if (!in_array($col, $existing)) $db->exec("ALTER TABLE product_categories ADD COLUMN $col $type");
        }

        // Posts columns (添加分类支持)
        $postCols = [
            'category_id' => 'INTEGER DEFAULT 0',
            'status' => 'TEXT DEFAULT "active"'
        ];
        $res = $db->query("PRAGMA table_info(posts)");
        $existing = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $existing[] = $row['name']; }
        foreach ($postCols as $col => $type) {
            if (!in_array($col, $existing)) $db->exec("ALTER TABLE posts ADD COLUMN $col $type");
        }
    }

    private static function initSchema(SQLite3 $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
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
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, created_at) VALUES (:u, :p, :c)");
        $stmt->bindValue(':u', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':p', $passwordHash, SQLITE3_TEXT);
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

