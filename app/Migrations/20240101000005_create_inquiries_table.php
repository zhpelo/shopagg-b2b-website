<?php
declare(strict_types=1);

/**
 * 迁移: 创建询单表
 * 版本: 20240101000005
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS inquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            company TEXT,
            phone TEXT,
            message TEXT,
            quantity TEXT,
            status TEXT DEFAULT "pending",
            ip TEXT,
            user_agent TEXT,
            source_url TEXT,
            created_at TEXT NOT NULL
        )');
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_inquiries_product ON inquiries(product_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_inquiries_status ON inquiries(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_inquiries_created ON inquiries(created_at)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS inquiries');
    }
};
