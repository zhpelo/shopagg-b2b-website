<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;

class Database {
    private static ?SQLite3 $instance = null;

    public static function getInstance(): SQLite3 {
        if (self::$instance === null) {
            $dbFile = APP_ROOT . '/#data/site.db';
            $isNew = !is_file($dbFile);
            if ($isNew) {
                @mkdir(APP_ROOT . '/#data/', 0755, true);
            }
            self::$instance = new SQLite3($dbFile);
            self::$instance->busyTimeout(5000);
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA journal_mode = WAL;');
            self::$instance->exec('PRAGMA busy_timeout = 5000;');
            
            // 使用迁移系统初始化/更新数据库
            self::runMigrations();
        }
        return self::$instance;
    }
    
    /**
     * 执行数据库迁移
     */
    private static function runMigrations(): void {
        $migrator = new Migrator();
        $migrator->runAllPending();
    }
}
