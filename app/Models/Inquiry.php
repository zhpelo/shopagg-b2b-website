<?php
declare(strict_types=1);

namespace App\Models;

class Inquiry extends BaseModel {
    public function getAll(): array {
        return $this->fetchAll("SELECT inquiries.*, products.title AS product_title 
            FROM inquiries LEFT JOIN products ON products.id = inquiries.product_id 
            ORDER BY inquiries.id DESC");
    }

    public function create(array $data): void {
        $stmt = $this->db->prepare("INSERT INTO inquiries (product_id, name, email, company, phone, message, created_at) 
            VALUES (:pid, :n, :e, :c, :p, :m, :t)");
        $stmt->bindValue(':pid', (int)($data['product_id'] ?? 0));
        $stmt->bindValue(':n', $data['name']);
        $stmt->bindValue(':e', $data['email']);
        $stmt->bindValue(':c', $data['company'] ?? '');
        $stmt->bindValue(':p', $data['phone'] ?? '');
        $stmt->bindValue(':m', $data['message'] ?? '');
        $stmt->bindValue(':t', gmdate('c'));
        $stmt->execute();
    }
}

