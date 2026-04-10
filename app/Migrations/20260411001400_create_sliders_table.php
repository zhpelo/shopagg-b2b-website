<?php
declare(strict_types=1);

/**
 * 迁移: 创建轮播图表
 * 版本: 20260411001400
 */

use SQLite3;

return new class {
    /**
     * 执行迁移
     */
    public function up(SQLite3 $db): void {
        // 创建轮播图区块表
        $db->exec('CREATE TABLE IF NOT EXISTS sliders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            status VARCHAR(20) DEFAULT "active",
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // 创建轮播图片表
        $db->exec('CREATE TABLE IF NOT EXISTS slider_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slider_id INTEGER NOT NULL,
            image VARCHAR(500) NOT NULL,
            title VARCHAR(255),
            subtitle VARCHAR(500),
            link_url VARCHAR(500),
            link_text VARCHAR(100) DEFAULT "View Details",
            sort_order INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT "active",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (slider_id) REFERENCES sliders(id) ON DELETE CASCADE
        )');

        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_sliders_slug ON sliders(slug)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_sliders_status ON sliders(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_slider_items_slider_id ON slider_items(slider_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_slider_items_status ON slider_items(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_slider_items_sort ON slider_items(sort_order)');

        // 插入默认的首页轮播图区块
        $stmt = $db->prepare('INSERT INTO sliders (name, slug, description, status, sort_order) VALUES (:name, :slug, :description, :status, :sort_order)');
        $stmt->bindValue(':name', '首页轮播图', SQLITE3_TEXT);
        $stmt->bindValue(':slug', 'home-hero', SQLITE3_TEXT);
        $stmt->bindValue(':description', '网站首页顶部的轮播图区块', SQLITE3_TEXT);
        $stmt->bindValue(':status', 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', 1, SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    /**
     * 回滚迁移
     */
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS slider_items');
        $db->exec('DROP TABLE IF EXISTS sliders');
    }
};
