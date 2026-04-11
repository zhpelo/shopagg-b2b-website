<?php
declare(strict_types=1);

/**
 * 迁移: 创建菜单表
 * 版本: 20260411001500
 */

return new class {
    /**
     * 执行迁移
     */
    public function up(SQLite3 $db): void {
        // 创建菜单表
        $db->exec('CREATE TABLE IF NOT EXISTS menus (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            status VARCHAR(20) DEFAULT "active",
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // 创建菜单项表（支持无限级）
        $db->exec('CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            menu_id INTEGER NOT NULL,
            parent_id INTEGER DEFAULT 0,
            title VARCHAR(255) NOT NULL,
            url VARCHAR(500) NOT NULL,
            target VARCHAR(20) DEFAULT "_self",
            css_class VARCHAR(100),
            sort_order INTEGER DEFAULT 0,
            status VARCHAR(20) DEFAULT "active",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
        )');

        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menus_slug ON menus(slug)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menus_status ON menus(status)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menu_items_menu_id ON menu_items(menu_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menu_items_parent_id ON menu_items(parent_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_menu_items_status ON menu_items(status)');

        // 插入默认的主导航菜单
        $stmt = $db->prepare('INSERT INTO menus (name, slug, description, status, sort_order) VALUES (:name, :slug, :description, :status, :sort_order)');
        $stmt->bindValue(':name', '主导航菜单', SQLITE3_TEXT);
        $stmt->bindValue(':slug', 'main-nav', SQLITE3_TEXT);
        $stmt->bindValue(':description', '网站顶部主导航', SQLITE3_TEXT);
        $stmt->bindValue(':status', 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', 1, SQLITE3_INTEGER);
        $stmt->execute();

        $menuId = $db->lastInsertRowID();

        // 插入默认菜单项
        $items = [
            ['title' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['title' => 'Products', 'url' => '/products', 'sort_order' => 2],
            ['title' => 'Cases', 'url' => '/cases', 'sort_order' => 3],
            ['title' => 'Blog', 'url' => '/blog', 'sort_order' => 4],
            ['title' => 'About Us', 'url' => '/about', 'sort_order' => 5],
            ['title' => 'Contact', 'url' => '/contact', 'sort_order' => 6],
        ];

        $itemStmt = $db->prepare('INSERT INTO menu_items (menu_id, parent_id, title, url, target, sort_order, status) VALUES (:menu_id, 0, :title, :url, "_self", :sort_order, "active")');
        foreach ($items as $item) {
            $itemStmt->bindValue(':menu_id', $menuId, SQLITE3_INTEGER);
            $itemStmt->bindValue(':title', $item['title'], SQLITE3_TEXT);
            $itemStmt->bindValue(':url', $item['url'], SQLITE3_TEXT);
            $itemStmt->bindValue(':sort_order', $item['sort_order'], SQLITE3_INTEGER);
            $itemStmt->execute();
        }
    }
    
    /**
     * 回滚迁移
     */
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS menu_items');
        $db->exec('DROP TABLE IF EXISTS menus');
    }
};
