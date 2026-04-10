<?php
declare(strict_types=1);

/**
 * 迁移: 创建产品价格表
 * 版本: 20240101000009
 */


return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS product_prices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            min_qty INTEGER NOT NULL,
            max_qty INTEGER,
            price REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT "USD",
            created_at TEXT NOT NULL,
            FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
        )');
        
        $db->exec('CREATE INDEX IF NOT EXISTS idx_product_prices_product ON product_prices(product_id)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS product_prices');
    }
};
