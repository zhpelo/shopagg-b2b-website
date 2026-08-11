<?php
declare(strict_types=1);

namespace App\Plugins;

use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

final class PluginDatabase {
    public function __construct(private string $pluginId, private SQLite3 $db) {}

    public function table(string $logicalName): string {
        if (preg_match('/^[a-z][a-z0-9_]{0,62}$/D', $logicalName) !== 1) {
            throw new \InvalidArgumentException('插件逻辑表名无效');
        }
        return 'p_' . str_replace('-', '_', $this->pluginId) . '_' . $logicalName;
    }

    public function prepare(string $sql): SQLite3Stmt|false { return $this->db->prepare($sql); }
    public function query(string $sql): SQLite3Result|false { return $this->db->query($sql); }
    public function exec(string $sql): bool { return $this->db->exec($sql); }
    public function lastInsertId(): int { return $this->db->lastInsertRowID(); }
    public function changes(): int { return $this->db->changes(); }

    public function fetchAll(string $sql, array $parameters = []): array {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) throw new \RuntimeException($this->db->lastErrorMsg());
        foreach ($parameters as $key => $value) $stmt->bindValue($key, $value, self::type($value));
        $result = $stmt->execute();
        if ($result === false) throw new \RuntimeException($this->db->lastErrorMsg());
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) $rows[] = $row;
        return $rows;
    }

    public function fetchOne(string $sql, array $parameters = []): ?array {
        return $this->fetchAll($sql, $parameters)[0] ?? null;
    }

    public function transaction(callable $callback): mixed {
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $result = $callback($this);
            if (!$this->db->exec('COMMIT')) throw new \RuntimeException($this->db->lastErrorMsg());
            return $result;
        } catch (\Throwable $e) {
            $this->db->exec('ROLLBACK');
            throw $e;
        }
    }

    private static function type(mixed $value): int {
        return match (true) {
            is_int($value), is_bool($value) => SQLITE3_INTEGER,
            is_float($value) => SQLITE3_FLOAT,
            $value === null => SQLITE3_NULL,
            default => SQLITE3_TEXT,
        };
    }
}
