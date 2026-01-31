<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use SQLite3;

abstract class BaseModel {
    protected SQLite3 $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /** 执行查询并返回结果集，失败时记录日志并返回 null */
    private function executeQuery(string $query, array $params = []): ?\SQLite3Result {
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            error_log("SQLite prepare failed: " . $this->db->lastErrorMsg() . " | Query: " . $query);
            return null;
        }
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, $this->getSqliteType($val));
        }
        $res = $stmt->execute();
        return $res !== false ? $res : null;
    }

    protected function fetchAll(string $query, array $params = []): array {
        $res = $this->executeQuery($query, $params);
        if ($res === null) return [];
        $list = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $list[] = $row;
        }
        return $list;
    }

    protected function fetchOne(string $query, array $params = []): ?array {
        $res = $this->executeQuery($query, $params);
        if ($res === null) return null;
        $row = $res->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    protected function getSqliteType($val): int {
        if (is_int($val)) return SQLITE3_INTEGER;
        if (is_float($val)) return SQLITE3_FLOAT;
        if ($val === null) return SQLITE3_NULL;
        return SQLITE3_TEXT;
    }
}

