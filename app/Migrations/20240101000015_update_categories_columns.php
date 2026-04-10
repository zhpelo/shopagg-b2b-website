<?php
declare(strict_types=1);

/**
 * 迁移: 更新分类表字段
 * 版本: 20240101000015
 */


return new class {
    public function up(SQLite3 $db): void {
        // 获取现有列
        $existingCols = [];
        $res = $db->query("PRAGMA table_info(product_categories)");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existingCols[] = $row['name'];
        }
        
        $columns = [
            'parent_id' => 'INTEGER DEFAULT 0',
            'type' => 'TEXT DEFAULT "product"',
            'sort_order' => 'INTEGER DEFAULT 0',
            'description' => 'TEXT',
        ];
        
        foreach ($columns as $col => $type) {
            if (!in_array($col, $existingCols)) {
                $db->exec("ALTER TABLE product_categories ADD COLUMN {$col} {$type}");
            }
        }
    }
    
    public function down(SQLite3 $db): void {
        // SQLite 不支持删除字段
    }
};
