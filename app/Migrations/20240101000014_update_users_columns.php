<?php
declare(strict_types=1);

/**
 * 迁移: 更新用户表字段
 * 版本: 20240101000014
 */


return new class {
    public function up(SQLite3 $db): void {
        // 获取现有列
        $existingCols = [];
        $res = $db->query("PRAGMA table_info(users)");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $existingCols[] = $row['name'];
        }
        
        $columns = [
            'role' => 'TEXT DEFAULT "staff"',
            'permissions' => 'TEXT',
            'display_name' => 'TEXT',
        ];
        
        foreach ($columns as $col => $type) {
            if (!in_array($col, $existingCols)) {
                $db->exec("ALTER TABLE users ADD COLUMN {$col} {$type}");
            }
        }
        
        // 确保 admin 用户角色正确
        $db->exec("UPDATE users SET role = 'admin' WHERE username = 'admin' AND (role IS NULL OR role != 'admin')");
    }
    
    public function down(SQLite3 $db): void {
        // SQLite 不支持删除字段
    }
};
