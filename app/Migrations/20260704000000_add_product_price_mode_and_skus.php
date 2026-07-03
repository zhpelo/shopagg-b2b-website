<?php
declare(strict_types=1);

/**
 * 迁移: 增加产品价格模式、多规格 SKU 价格和全局货币设置
 * 版本: 20260704000000
 */

return new class {
    public function up(SQLite3 $db): void {
        if ($this->tableExists($db, 'products') && !$this->hasColumn($db, 'products', 'price_mode')) {
            $db->exec('ALTER TABLE products ADD COLUMN price_mode TEXT NOT NULL DEFAULT "tier"');
        }

        $db->exec('CREATE TABLE IF NOT EXISTS product_skus (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            sku_name TEXT NOT NULL,
            min_qty INTEGER NOT NULL,
            price REAL NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
        )');

        $db->exec('CREATE INDEX IF NOT EXISTS idx_product_skus_product ON product_skus(product_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_product_skus_sort ON product_skus(product_id, sort_order)');

        if ($this->tableExists($db, 'settings')) {
            $stmt = $db->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)');
            $stmt->bindValue(':key', 'site_currency', SQLITE3_TEXT);
            $stmt->bindValue(':value', 'USD', SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS product_skus');

        if ($this->tableExists($db, 'settings')) {
            $stmt = $db->prepare('DELETE FROM settings WHERE key = :key');
            $stmt->bindValue(':key', 'site_currency', SQLITE3_TEXT);
            $stmt->execute();
        }

        if (!$this->tableExists($db, 'products') || !$this->hasColumn($db, 'products', 'price_mode')) {
            return;
        }

        $hasDeletedAt = $this->hasColumn($db, 'products', 'deleted_at');

        $db->exec('PRAGMA foreign_keys = OFF');

        $deletedAtColumn = $hasDeletedAt ? ",\n            deleted_at TEXT" : '';
        $deletedAtInsert = $hasDeletedAt ? ', deleted_at' : '';

        $db->exec('CREATE TABLE products_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            summary TEXT,
            content TEXT,
            category_id INTEGER DEFAULT 0,
            status TEXT DEFAULT "active",
            product_type TEXT,
            vendor TEXT,
            tags TEXT,
            images_json TEXT,
            seo_title TEXT,
            seo_keywords TEXT,
            seo_description TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL' . $deletedAtColumn . '
        )');

        $db->exec('INSERT INTO products_new (
            id, title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, seo_title, seo_keywords, seo_description, created_at, updated_at' . $deletedAtInsert . '
        )
        SELECT
            id, title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, seo_title, seo_keywords, seo_description, created_at, updated_at' . $deletedAtInsert . '
        FROM products');

        $db->exec('DROP TABLE products');
        $db->exec('ALTER TABLE products_new RENAME TO products');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug)');
        if ($hasDeletedAt) {
            $db->exec('CREATE INDEX IF NOT EXISTS idx_products_deleted_at ON products(deleted_at)');
        }

        $db->exec('PRAGMA foreign_keys = ON');
    }

    private function tableExists(SQLite3 $db, string $table): bool {
        $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
        $stmt->bindValue(':table', $table, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC) !== false;
    }

    private function hasColumn(SQLite3 $db, string $table, string $column): bool {
        $result = $db->query('PRAGMA table_info(' . $table . ')');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (($row['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }
};
