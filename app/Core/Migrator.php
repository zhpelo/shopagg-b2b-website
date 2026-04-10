<?php
declare(strict_types=1);

namespace App\Core;

use SQLite3;

/**
 * 数据库迁移管理器
 * 
 * 管理数据库结构变更，支持版本化迁移和自动执行
 */
class Migrator {
    
    /** 迁移文件目录 */
    private string $migrationsDir;
    
    /** 数据库实例 */
    private SQLite3 $db;
    
    /** 迁移表名 */
    private const MIGRATIONS_TABLE = 'migrations';
    
    public function __construct() {
        $this->migrationsDir = APP_ROOT . '/app/Migrations';
        $this->db = Database::getInstance();
        $this->ensureMigrationsTable();
    }
    
    /**
     * 确保迁移记录表存在
     */
    private function ensureMigrationsTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS " . self::MIGRATIONS_TABLE . " (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            execution_time INTEGER DEFAULT 0,
            batch INTEGER DEFAULT 0
        )");
    }
    
    /**
     * 获取所有迁移文件
     * 
     * @return array 按版本号排序的迁移文件列表
     */
    public function getAllMigrations(): array {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }
        
        $files = glob($this->migrationsDir . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            // 文件名格式: YYYYMMDDHHMMSS_description.php 或 V1_0_1_description.php
            if (preg_match('/^(\d{14}|V\d+[\d_]+)_(.+?)\.php$/', $filename, $matches)) {
                $version = $matches[1];
                $name = str_replace('_', ' ', $matches[2]);
                $migrations[] = [
                    'version' => $version,
                    'name' => $name,
                    'filename' => $filename,
                    'filepath' => $file,
                ];
            }
        }
        
        // 按版本号排序
        usort($migrations, fn($a, $b) => strcmp($a['version'], $b['version']));
        
        return $migrations;
    }
    
    /**
     * 获取已执行的迁移
     * 
     * @return array 已执行的迁移版本列表
     */
    public function getExecutedMigrations(): array {
        $result = $this->db->query("SELECT version, executed_at, execution_time FROM " . self::MIGRATIONS_TABLE . " ORDER BY id");
        $migrations = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $migrations[$row['version']] = $row;
        }
        return $migrations;
    }
    
    /**
     * 获取待执行的迁移
     * 
     * @return array 未执行的迁移列表
     */
    public function getPendingMigrations(): array {
        $allMigrations = $this->getAllMigrations();
        $executed = $this->getExecutedMigrations();
        
        return array_filter($allMigrations, fn($m) => !isset($executed[$m['version']]));
    }
    
    /**
     * 执行单个迁移
     * 
     * @param array $migration 迁移信息
     * @param int $batch 批次号
     * @return array 执行结果
     */
    public function runMigration(array $migration, int $batch = 0): array {
        $startTime = microtime(true);
        
        try {
            // 加载迁移文件
            $migrationClass = require $migration['filepath'];
            
            // 检查是否是有效的迁移类
            if (!is_object($migrationClass) || !method_exists($migrationClass, 'up')) {
                return [
                    'success' => false,
                    'message' => '无效的迁移文件：缺少 up() 方法',
                ];
            }
            
            // 执行迁移
            $migrationClass->up($this->db);
            
            // 记录迁移
            $executionTime = (int)((microtime(true) - $startTime) * 1000);
            $this->recordMigration($migration['version'], $migration['name'], $executionTime, $batch);
            
            return [
                'success' => true,
                'message' => '迁移成功',
                'execution_time' => $executionTime,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '迁移失败：' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * 执行所有待处理的迁移
     * 
     * @return array 执行结果
     */
    public function runAllPending(): array {
        $pending = $this->getPendingMigrations();
        
        if (empty($pending)) {
            return [
                'success' => true,
                'message' => '没有待执行的迁移',
                'executed' => [],
            ];
        }
        
        // 获取当前批次号
        $batch = $this->getNextBatchNumber();
        
        $executed = [];
        $errors = [];
        
        foreach ($pending as $migration) {
            $result = $this->runMigration($migration, $batch);
            
            if ($result['success']) {
                $executed[] = [
                    'version' => $migration['version'],
                    'name' => $migration['name'],
                    'execution_time' => $result['execution_time'],
                ];
            } else {
                $errors[] = [
                    'version' => $migration['version'],
                    'name' => $migration['name'],
                    'error' => $result['message'],
                ];
                // 遇到错误停止执行
                break;
            }
        }
        
        return [
            'success' => empty($errors),
            'message' => empty($errors) 
                ? '成功执行 ' . count($executed) . ' 个迁移' 
                : '执行到 ' . $migration['version'] . ' 时失败：' . $result['message'],
            'executed' => $executed,
            'errors' => $errors,
        ];
    }
    
    /**
     * 回滚最近一次批次的迁移
     * 
     * @return array 回滚结果
     */
    public function rollback(): array {
        $lastBatch = $this->getLastBatchNumber();
        
        if ($lastBatch === 0) {
            return [
                'success' => true,
                'message' => '没有可回滚的迁移',
                'rolled_back' => [],
            ];
        }
        
        // 获取最近一次批次的迁移
        $stmt = $this->db->prepare("SELECT version, name FROM " . self::MIGRATIONS_TABLE . " WHERE batch = :batch ORDER BY id DESC");
        $stmt->bindValue(':batch', $lastBatch, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $migrations = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $migrations[] = $row;
        }
        
        $rolledBack = [];
        $errors = [];
        
        foreach ($migrations as $migrationInfo) {
            $filepath = $this->migrationsDir . '/' . $migrationInfo['version'] . '_*.php';
            $files = glob($filepath);
            
            if (empty($files)) {
                // 如果文件不存在，只删除记录
                $this->removeMigrationRecord($migrationInfo['version']);
                $rolledBack[] = $migrationInfo;
                continue;
            }
            
            $migrationFile = $files[0];
            $migrationClass = require $migrationFile;
            
            if (is_object($migrationClass) && method_exists($migrationClass, 'down')) {
                try {
                    $migrationClass->down($this->db);
                } catch (\Exception $e) {
                    $errors[] = [
                        'version' => $migrationInfo['version'],
                        'error' => $e->getMessage(),
                    ];
                    continue;
                }
            }
            
            $this->removeMigrationRecord($migrationInfo['version']);
            $rolledBack[] = $migrationInfo;
        }
        
        return [
            'success' => empty($errors),
            'message' => empty($errors) 
                ? '成功回滚 ' . count($rolledBack) . ' 个迁移' 
                : '回滚过程中出现错误',
            'rolled_back' => $rolledBack,
            'errors' => $errors,
        ];
    }
    
    /**
     * 获取迁移状态统计
     * 
     * @return array 状态信息
     */
    public function getStatus(): array {
        $all = $this->getAllMigrations();
        $executed = $this->getExecutedMigrations();
        $pending = array_filter($all, fn($m) => !isset($executed[$m['version']]));
        
        return [
            'total' => count($all),
            'executed' => count($executed),
            'pending' => count($pending),
            'last_executed' => !empty($executed) ? array_key_last($executed) : null,
        ];
    }
    
    /**
     * 创建新的迁移文件
     * 
     * @param string $name 迁移名称（如 add_user_table）
     * @return array 创建结果
     */
    public function createMigration(string $name): array {
        // 确保目录存在
        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
        }
        
        // 生成版本号
        $version = date('YmdHis');
        
        // 清理名称
        $safeName = preg_replace('/[^a-zA-Z0-9_]+/', '_', $name);
        $safeName = trim($safeName, '_');
        
        $filename = "{$version}_{$safeName}.php";
        $filepath = $this->migrationsDir . '/' . $filename;
        
        if (is_file($filepath)) {
            return [
                'success' => false,
                'message' => '迁移文件已存在',
            ];
        }
        
        // 生成迁移模板
        $template = $this->getMigrationTemplate($version, $name);
        file_put_contents($filepath, $template);
        
        return [
            'success' => true,
            'message' => '迁移文件已创建',
            'filepath' => $filepath,
            'filename' => $filename,
        ];
    }
    
    /**
     * 获取迁移文件模板
     */
    private function getMigrationTemplate(string $version, string $name): string {
        return <<<PHP
<?php
declare(strict_types=1);

/**
 * 迁移: {$name}
 * 版本: {$version}
 */

use SQLite3;

return new class {
    /**
     * 执行迁移
     */
    public function up(SQLite3 \$db): void {
        // 在此编写升级逻辑
        // 示例:
        // \$db->exec('CREATE TABLE IF NOT EXISTS new_table (
        //     id INTEGER PRIMARY KEY AUTOINCREMENT,
        //     name TEXT NOT NULL,
        //     created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        // )');
        
        // 示例: 添加字段
        // \$db->exec('ALTER TABLE existing_table ADD COLUMN new_field TEXT');
    }
    
    /**
     * 回滚迁移
     */
    public function down(SQLite3 \$db): void {
        // 在此编写回滚逻辑
        // 示例:
        // \$db->exec('DROP TABLE IF EXISTS new_table');
        
        // 注意: SQLite 不支持直接删除字段，需要重建表
    }
};
PHP;
    }
    
    /**
     * 记录迁移执行
     */
    private function recordMigration(string $version, string $name, int $executionTime, int $batch): void {
        $stmt = $this->db->prepare("INSERT INTO " . self::MIGRATIONS_TABLE . " (version, name, execution_time, batch) VALUES (:version, :name, :time, :batch)");
        $stmt->bindValue(':version', $version, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':time', $executionTime, SQLITE3_INTEGER);
        $stmt->bindValue(':batch', $batch, SQLITE3_INTEGER);
        $stmt->execute();
    }
    
    /**
     * 删除迁移记录
     */
    private function removeMigrationRecord(string $version): void {
        $stmt = $this->db->prepare("DELETE FROM " . self::MIGRATIONS_TABLE . " WHERE version = :version");
        $stmt->bindValue(':version', $version, SQLITE3_TEXT);
        $stmt->execute();
    }
    
    /**
     * 获取下一个批次号
     */
    private function getNextBatchNumber(): int {
        $result = $this->db->query("SELECT MAX(batch) as max_batch FROM " . self::MIGRATIONS_TABLE);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return ($row['max_batch'] ?? 0) + 1;
    }
    
    /**
     * 获取最后一批次号
     */
    private function getLastBatchNumber(): int {
        $result = $this->db->query("SELECT MAX(batch) as max_batch FROM " . self::MIGRATIONS_TABLE);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row['max_batch'] ?? 0;
    }
}
