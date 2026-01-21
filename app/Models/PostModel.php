<?php
declare(strict_types=1);

namespace App\Models;

class PostModel extends BaseModel {
    public function getList(int $limit = 0, bool $activeOnly = false): array {
        $query = "SELECT posts.*, product_categories.name AS category_name
            FROM posts 
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'";
        if ($activeOnly) {
            $query .= " WHERE posts.status = 'active'";
        }
        $query .= " ORDER BY posts.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        return $this->fetchAll($query);
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT posts.*, product_categories.name AS category_name
            FROM posts 
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.id = :id", [':id' => $id]);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO posts (title, slug, summary, content, category_id, status, created_at, updated_at) VALUES (:t, :s, :sum, :c, :cid, :st, :ca, :ua)");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)($data['category_id'] ?? 0));
        $stmt->bindValue(':st', $data['status'] ?? 'active');
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE posts SET title=:t, slug=:s, summary=:sum, content=:c, category_id=:cid, status=:st, updated_at=:ua WHERE id=:id");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)($data['category_id'] ?? 0));
        $stmt->bindValue(':st', $data['status'] ?? 'active');
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function getBySlug(string $slug): ?array {
        return $this->fetchOne("SELECT posts.*, product_categories.name AS category_name
            FROM posts 
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.slug = :s", [':s' => $slug]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function getByCategory(int $categoryId, int $limit = 0): array {
        $query = "SELECT posts.*, product_categories.name AS category_name
            FROM posts 
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.category_id = :cid AND posts.status = 'active'
            ORDER BY posts.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        return $this->fetchAll($query, [':cid' => $categoryId]);
    }
}
