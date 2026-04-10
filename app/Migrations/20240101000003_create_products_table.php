<?php
declare(strict_types=1);

/**
 * 迁移: 创建产品表
 * 版本: 20240101000003
 */


return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS products (
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
        
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS products');
    }
};
