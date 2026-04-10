<?php
declare(strict_types=1);

/**
 * 迁移: 创建媒体文件表
 * 版本: 20240101000009
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS media_files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            original_name TEXT NOT NULL,
            storage_name TEXT NOT NULL,
            title TEXT,
            alt_text TEXT,
            directory TEXT NOT NULL DEFAULT "",
            public_path TEXT NOT NULL UNIQUE,
            media_type TEXT NOT NULL DEFAULT "image",
            mime_type TEXT,
            extension TEXT,
            size INTEGER NOT NULL DEFAULT 0,
            width INTEGER NOT NULL DEFAULT 0,
            height INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )');
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_media_files_directory ON media_files(directory)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_media_files_media_type ON media_files(media_type)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_media_files_created_at ON media_files(created_at DESC)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_media_files_original_name ON media_files(original_name)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS media_files');
    }
};
