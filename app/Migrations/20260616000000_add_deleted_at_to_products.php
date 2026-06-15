<?php
declare(strict_types=1);

/**
 * 迁移: 为产品表增加软删除字段
 * 版本: 20260616000000
 */

return new class {
    public function up(SQLite3 $db): void {
        if (!$this->tableExists($db, 'products')) {
            return;
        }

        if (!$this->hasColumn($db, 'products', 'deleted_at')) {
            $db->exec('ALTER TABLE products ADD COLUMN deleted_at TEXT');
        }
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_deleted_at ON products(deleted_at)');
    }

    public function down(SQLite3 $db): void {
        if (!$this->tableExists($db, 'products') || !$this->hasColumn($db, 'products', 'deleted_at')) {
            return;
        }

        $db->exec('PRAGMA foreign_keys = OFF');

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
            updated_at TEXT NOT NULL
        )');

        $db->exec('INSERT INTO products_new (
            id, title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, seo_title, seo_keywords, seo_description, created_at, updated_at
        )
        SELECT
            id, title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, seo_title, seo_keywords, seo_description, created_at, updated_at
        FROM products');

        $db->exec('DROP TABLE products');
        $db->exec('ALTER TABLE products_new RENAME TO products');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug)');

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
