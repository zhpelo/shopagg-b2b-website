<?php
declare(strict_types=1);

namespace App\Models;

class CaseModel extends BaseModel {
    public function getList(int $limit = 0): array {
        $query = "SELECT * FROM cases ORDER BY id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        return $this->fetchAll($query);
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM cases WHERE id = :id", [':id' => $id]);
    }

    public function create(array $data): void {
        $stmt = $this->db->prepare("INSERT INTO cases (title, slug, summary, content, seo_title, seo_keywords, seo_description, created_at, updated_at) VALUES (:t, :s, :sum, :c, :seot, :seok, :seod, :ca, :ua)");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE cases SET title=:t, slug=:s, summary=:sum, content=:c, seo_title=:seot, seo_keywords=:seok, seo_description=:seod, updated_at=:ua WHERE id=:id");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function getBySlug(string $slug): ?array {
        return $this->fetchOne("SELECT * FROM cases WHERE slug = :s", [':s' => $slug]);
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM cases WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }
}
