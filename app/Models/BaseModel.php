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

    protected function fetchAll(string $query, array $params = []): array {
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, $this->getSqliteType($val));
        }
        $res = $stmt->execute();
        $list = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $list[] = $row;
        }
        return $list;
    }

    protected function fetchOne(string $query, array $params = []): ?array {
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, $this->getSqliteType($val));
        }
        $res = $stmt->execute();
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

