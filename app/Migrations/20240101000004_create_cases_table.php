<?php
declare(strict_types=1);

/**
 * 迁移: 创建案例表（已废弃，迁移到 posts 表）
 * 版本: 20240101000004
 */


return new class {
    public function up(SQLite3 $db): void {
        // cases 表已废弃，数据已迁移到 posts 表
        // 保留空迁移以记录历史
    }
    
    public function down(SQLite3 $db): void {
        // 不需要回滚
    }
};
