<?php
declare(strict_types=1);

namespace App\Models;

class Inquiry extends BaseModel {
    public function getList(array $params = []): array {
        $where = "WHERE 1=1";
        if (!empty($params['status'])) {
            $where .= " AND inquiries.status = :status";
        }
        
        $sql = "SELECT inquiries.*, products.title AS product_title 
                FROM inquiries LEFT JOIN products ON products.id = inquiries.product_id 
                $where
                ORDER BY inquiries.id DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params['status'])) {
            $stmt->bindValue(':status', $params['status']);
        }
        
        $res = $stmt->execute();
        if (!$res) return [];
        $items = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) { $items[] = $row; }
        return $items;
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT inquiries.*, products.title AS product_title 
            FROM inquiries LEFT JOIN products ON products.id = inquiries.product_id 
            WHERE inquiries.id = :id", [':id' => $id]);
    }

    public function updateStatus(int $id, string $status): void {
        $stmt = $this->db->prepare("UPDATE inquiries SET status = :s WHERE id = :id");
        $stmt->bindValue(':s', $status);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function create(array $data): void {
        $stmt = $this->db->prepare("INSERT INTO inquiries (product_id, name, email, company, phone, message, quantity, ip, user_agent, source_url, created_at, status) 
            VALUES (:pid, :n, :e, :c, :p, :m, :q, :ip, :ua, :url, :t, 'pending')");
        $stmt->bindValue(':pid', (int)($data['product_id'] ?? 0));
        $stmt->bindValue(':n', $data['name']);
        $stmt->bindValue(':e', $data['email']);
        $stmt->bindValue(':c', $data['company'] ?? '');
        $stmt->bindValue(':p', $data['phone'] ?? '');
        $stmt->bindValue(':m', $data['message'] ?? '');
        $stmt->bindValue(':q', $data['quantity'] ?? '');
        $stmt->bindValue(':ip', $data['ip'] ?? '');
        $stmt->bindValue(':ua', $data['user_agent'] ?? '');
        $stmt->bindValue(':url', $data['source_url'] ?? '');
        $stmt->bindValue(':t', gmdate('c'));
        $stmt->execute();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM inquiries WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}

