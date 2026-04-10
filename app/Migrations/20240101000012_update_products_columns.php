<?php
declare(strict_types=1);

/**
 * 迁移: 更新产品表字段
 * 版本: 20240101000012
 */


return new class {
    public function up(SQLite3 $db): void {
        // 获取现有列
        $existingCols = [];
        $res = $db->query("PRAGMA table_info(products)");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existingCols[] = $row['name'];
        }
        
        $columns = [
            'status' => 'TEXT DEFAULT "active"',
            'product_type' => 'TEXT',
            'vendor' => 'TEXT',
            'tags' => 'TEXT',
            'images_json' => 'TEXT',
            'banner_image' => 'TEXT',
            'seo_title' => 'TEXT',
            'seo_keywords' => 'TEXT',
            'seo_description' => 'TEXT',
        ];
        
        foreach ($columns as $col => $type) {
            if (!in_array($col, $existingCols)) {
                $db->exec("ALTER TABLE products ADD COLUMN {$col} {$type}");
            }
        }
    }
    
    public function down(SQLite3 $db): void {
        // SQLite 不支持删除字段
    }
};
