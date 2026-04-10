<?php
declare(strict_types=1);

/**
 * 迁移: 创建产品分类表
 * 版本: 20240101000008
 */


return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS product_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            parent_id INTEGER DEFAULT 0,
            type TEXT DEFAULT "product",
            sort_order INTEGER DEFAULT 0,
            description TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )');
        
        $db->exec('CREATE INDEX IF NOT EXISTS idx_categories_parent ON product_categories(parent_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_categories_type ON product_categories(type)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_categories_slug ON product_categories(slug)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS product_categories');
    }
};
