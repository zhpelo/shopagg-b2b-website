<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use SQLite3;

class User {
    private SQLite3 $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $res = $this->db->query("SELECT * FROM users ORDER BY id ASC");
        $users = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        return $users;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $res = $stmt->execute();
        return $res->fetchArray(SQLITE3_ASSOC) ?: null;
    }

    public function getByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->bindValue(':u', $username, SQLITE3_TEXT);
        $res = $stmt->execute();
        return $res->fetchArray(SQLITE3_ASSOC) ?: null;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, password_hash, role, permissions, display_name, created_at)
            VALUES (:u, :p, :r, :perms, :d, :c)
        ");
        $stmt->bindValue(':u', $data['username'], SQLITE3_TEXT);
        $stmt->bindValue(':p', password_hash($data['password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
        $stmt->bindValue(':r', $data['role'] ?? 'staff', SQLITE3_TEXT);
        $stmt->bindValue(':perms', $data['permissions'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':d', $data['display_name'] ?? $data['username'], SQLITE3_TEXT);
        $stmt->bindValue(':c', gmdate('c'), SQLITE3_TEXT);
        return $stmt->execute() instanceof \SQLite3Result;
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        if (isset($data['username'])) $fields[] = "username = :u";
        if (isset($data['password']) && !empty($data['password'])) $fields[] = "password_hash = :p";
        if (isset($data['role'])) $fields[] = "role = :r";
        if (isset($data['permissions'])) $fields[] = "permissions = :perms";
        if (isset($data['display_name'])) $fields[] = "display_name = :d";

        if (empty($fields)) return true;

        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        if (isset($data['username'])) $stmt->bindValue(':u', $data['username'], SQLITE3_TEXT);
        if (isset($data['password']) && !empty($data['password'])) {
            $stmt->bindValue(':p', password_hash($data['password'], PASSWORD_DEFAULT), SQLITE3_TEXT);
        }
        if (isset($data['role'])) $stmt->bindValue(':r', $data['role'], SQLITE3_TEXT);
        if (isset($data['permissions'])) $stmt->bindValue(':perms', $data['permissions'], SQLITE3_TEXT);
        if (isset($data['display_name'])) $stmt->bindValue(':d', $data['display_name'], SQLITE3_TEXT);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

        return $stmt->execute() instanceof \SQLite3Result;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        return $stmt->execute() instanceof \SQLite3Result;
    }
}

