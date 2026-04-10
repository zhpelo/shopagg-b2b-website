<?php
declare(strict_types=1);

/**
 * 迁移: 更新文章表字段
 * 版本: 20240101000016
 */


return new class {
    public function up(SQLite3 $db): void {
        // 获取现有列
        $existingCols = [];
        $res = $db->query("PRAGMA table_info(posts)");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existingCols[] = $row['name'];
        }
        
        $columns = [
            'post_type' => 'TEXT DEFAULT "post"',
            'category_id' => 'INTEGER DEFAULT 0',
            'status' => 'TEXT DEFAULT "active"',
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ];
        
        foreach ($columns as $col => $type) {
            if (!in_array($col, $existingCols)) {
                $db->exec("ALTER TABLE posts ADD COLUMN {$col} {$type}");
            }
        }
        
        // 修复空的 post_type
        $db->exec("UPDATE posts SET post_type = 'post' WHERE post_type IS NULL OR TRIM(post_type) = ''");
        
        // 创建索引
        $db->exec('CREATE INDEX IF NOT EXISTS idx_posts_post_type ON posts(post_type)');
    }
    
    public function down(SQLite3 $db): void {
        // SQLite 不支持删除字段
    }
};
