<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;

class Database {
    private static ?SQLite3 $instance = null;

    public static function getInstance(): SQLite3 {
        if (self::$instance === null) {
            $dbFile = APP_ROOT . '/storage/site.db';
            $storageDir = APP_ROOT . '/storage';
            $isNew = !is_file($dbFile);
            
            if ($isNew) {
                @mkdir($storageDir, 0755, true);
                
                // 确保 storage 目录下有 .htaccess 保护
                self::ensureDataDirProtection($storageDir);
            }
            
            self::$instance = new SQLite3($dbFile);
            self::$instance->busyTimeout(5000);
            self::$instance->exec('PRAGMA foreign_keys = ON;');
            self::$instance->exec('PRAGMA journal_mode = WAL;');
            self::$instance->exec('PRAGMA busy_timeout = 5000;');
            
            // 使用迁移系统初始化/更新数据库
            self::runMigrations();
            
            // 确保数据库文件权限安全（仅在新创建时）
            if ($isNew && is_file($dbFile)) {
                chmod($dbFile, 0640);
            }
        }
        return self::$instance;
    }
    
    /**
     * 确保 storage 目录有安全保护
     */
    private static function ensureDataDirProtection(string $dataDir): void {
        // 创建 .htaccess 文件防止直接访问
        $htaccessFile = $dataDir . '/.htaccess';
        if (!is_file($htaccessFile)) {
            $htaccessContent = <<<'HTACCESS'
# 禁止访问此目录下的所有文件
Order deny,allow
Deny from all

<IfModule mod_authz_core.c>
  Require all denied
</IfModule>

Options -Indexes
HTACCESS;
            file_put_contents($htaccessFile, $htaccessContent);
        }
        
        // 创建 index.html 防止目录列表
        $indexFile = $dataDir . '/index.html';
        if (!is_file($indexFile)) {
            file_put_contents($indexFile, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Directory access is forbidden.</p></body></html>');
        }
    }
    
    /**
     * 执行数据库迁移
     */
    private static function runMigrations(): void {
        $migrator = new Migrator();
        $migrator->runAllPending();
    }
}
