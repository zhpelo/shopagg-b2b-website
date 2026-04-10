<?php
declare(strict_types=1);

/**
 * 迁移: 更新询单表字段
 * 版本: 20240101000013
 */


return new class {
    public function up(SQLite3 $db): void {
        // 获取现有列
        $existingCols = [];
        $res = $db->query("PRAGMA table_info(inquiries)");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existingCols[] = $row['name'];
        }
        
        $columns = [
            'quantity' => 'TEXT',
            'status' => 'TEXT DEFAULT "pending"',
            'ip' => 'TEXT',
            'user_agent' => 'TEXT',
            'source_url' => 'TEXT',
        ];
        
        foreach ($columns as $col => $type) {
            if (!in_array($col, $existingCols)) {
                $db->exec("ALTER TABLE inquiries ADD COLUMN {$col} {$type}");
            }
        }
    }
    
    public function down(SQLite3 $db): void {
        // SQLite 不支持删除字段
    }
};
