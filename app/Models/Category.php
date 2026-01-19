<?php
declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel {
    public function getAll(): array {
        return $this->fetchAll("SELECT * FROM product_categories ORDER BY id DESC");
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM product_categories WHERE id = :id", [':id' => $id]);
    }

    public function create(string $name, string $slug): void {
        $stmt = $this->db->prepare("INSERT INTO product_categories (name, slug, created_at, updated_at) VALUES (:n, :s, :ca, :ua)");
        $stmt->bindValue(':n', $name);
        $stmt->bindValue(':s', $slug);
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
    }

    public function update(int $id, string $name, string $slug): void {
        $stmt = $this->db->prepare("UPDATE product_categories SET name = :n, slug = :s, updated_at = :ua WHERE id = :id");
        $stmt->bindValue(':n', $name);
        $stmt->bindValue(':s', $slug);
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function delete(int $id): void {
        $this->db->exec("UPDATE products SET category_id = 0 WHERE category_id = $id");
        $stmt = $this->db->prepare("DELETE FROM product_categories WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
