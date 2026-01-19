<?php
declare(strict_types=1);

namespace App\Models;

class PostModel extends BaseModel {
    public function getList(int $limit = 0): array {
        $query = "SELECT * FROM posts ORDER BY id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        return $this->fetchAll($query);
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM posts WHERE id = :id", [':id' => $id]);
    }

    public function create(array $data): void {
        $stmt = $this->db->prepare("INSERT INTO posts (title, slug, summary, content, created_at, updated_at) VALUES (:t, :s, :sum, :c, :ca, :ua)");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE posts SET title=:t, slug=:s, summary=:sum, content=:c, updated_at=:ua WHERE id=:id");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
