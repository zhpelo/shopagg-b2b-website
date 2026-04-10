<?php
declare(strict_types=1);

/**
 * 迁移: 创建文章/页面/案例表
 * 版本: 20260410000004
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            post_type TEXT NOT NULL DEFAULT "post",
            summary TEXT,
            content TEXT,
            cover TEXT,
            category_id INTEGER DEFAULT 0,
            status TEXT DEFAULT "active",
            seo_title TEXT,
            seo_keywords TEXT,
            seo_description TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )');
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_posts_post_type ON posts(post_type)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug)');
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS posts');
    }
};
