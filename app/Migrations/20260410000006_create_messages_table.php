<?php
declare(strict_types=1);

/**
 * 迁移: 创建留言表
 * 版本: 20260410000006
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            company TEXT,
            phone TEXT,
            message TEXT,
            created_at TEXT NOT NULL
        )');
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_messages_created ON messages(created_at)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS messages');
    }
};
