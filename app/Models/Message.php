<?php
declare(strict_types=1);

namespace App\Models;

class Message extends BaseModel {
    public function getAll(): array {
        return $this->fetchAll("SELECT * FROM messages ORDER BY id DESC");
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM messages WHERE id = :id", [':id' => $id]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function create(array $data): void {
        $stmt = $this->db->prepare("INSERT INTO messages (name, email, company, phone, message, created_at) 
            VALUES (:n, :e, :c, :p, :m, :t)");
        $stmt->bindValue(':n', $data['name']);
        $stmt->bindValue(':e', $data['email']);
        $stmt->bindValue(':c', $data['company'] ?? '');
        $stmt->bindValue(':p', $data['phone'] ?? '');
        $stmt->bindValue(':m', $data['message']);
        $stmt->bindValue(':t', gmdate('c'));
        $stmt->execute();
    }
}

