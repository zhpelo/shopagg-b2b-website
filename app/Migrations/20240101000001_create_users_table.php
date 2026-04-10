<?php
declare(strict_types=1);

/**
 * 迁移: 创建用户表
 * 版本: 20240101000001
 */


return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT "staff",
            permissions TEXT,
            display_name TEXT,
            created_at TEXT NOT NULL
        )');
        
        // 创建默认管理员
        $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, role, display_name, created_at) VALUES (:u, :p, :r, :d, :c)");
        $stmt->bindValue(':u', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':p', $passwordHash, SQLITE3_TEXT);
        $stmt->bindValue(':r', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':d', 'Administrator', SQLITE3_TEXT);
        $stmt->bindValue(':c', gmdate('c'), SQLITE3_TEXT);
        $stmt->execute();
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS users');
    }
};
