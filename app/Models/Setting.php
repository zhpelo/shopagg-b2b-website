<?php
declare(strict_types=1);

namespace App\Models;

class Setting extends BaseModel {
    public function get(string $key, string $default = ''): string {
        $row = $this->fetchOne("SELECT value FROM settings WHERE key = :k", [':k' => $key]);
        return $row ? $row['value'] : $default;
    }

    public function set(string $key, string $value): void {
        $stmt = $this->db->prepare("INSERT INTO settings (key, value) VALUES (:k, :v)
            ON CONFLICT(key) DO UPDATE SET value = excluded.value");
        $stmt->bindValue(':k', $key);
        $stmt->bindValue(':v', $value);
        $stmt->execute();
    }

    public function getAll(): array {
        $list = [];
        $rows = $this->fetchAll("SELECT * FROM settings");
        foreach ($rows as $row) {
            $list[$row['key']] = $row['value'];
        }
        return $list;
    }
}

