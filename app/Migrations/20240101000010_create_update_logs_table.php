<?php
declare(strict_types=1);

/**
 * 迁移: 创建更新日志表
 * 版本: 20240101000010
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS update_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version VARCHAR(50) NOT NULL,
            release_name VARCHAR(255),
            release_body TEXT,
            download_url TEXT,
            backup_path TEXT,
            files_updated INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT "success",
            error_message TEXT,
            installed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            installed_by INTEGER DEFAULT 0
        )');
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_update_logs_version ON update_logs(version)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_update_logs_status ON update_logs(status)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS update_logs');
    }
};
