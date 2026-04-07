<?php
declare(strict_types=1);

namespace App\Models;

class PostModel extends BaseModel {
    public function getList(int $limit = 0, bool $activeOnly = false, string $type = 'post'): array {
        $params = [':post_type' => $this->normalizeType($type)];
        $query = "SELECT posts.*, product_categories.name AS category_name
            FROM posts
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.post_type = :post_type";
        if ($activeOnly) {
            $query .= " AND posts.status = 'active'";
        }
        $query .= " ORDER BY posts.id DESC";
        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        return $this->fetchAll($query, $params);
    }

    public function getById(int $id, ?string $type = 'post'): ?array {
        $params = [':id' => $id];
        $query = "SELECT posts.*, product_categories.name AS category_name
            FROM posts
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.id = :id";
        if ($type !== null) {
            $query .= " AND posts.post_type = :post_type";
            $params[':post_type'] = $this->normalizeType($type);
        }
        return $this->fetchOne($query, $params);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO posts (
                title, slug, post_type, summary, content, cover, category_id, status,
                seo_title, seo_keywords, seo_description, created_at, updated_at
            ) VALUES (
                :title, :slug, :post_type, :summary, :content, :cover, :category_id, :status,
                :seo_title, :seo_keywords, :seo_description, :created_at, :updated_at
            )
        ");
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':post_type', $this->normalizeType((string)($data['post_type'] ?? 'post')));
        $stmt->bindValue(':summary', $data['summary']);
        $stmt->bindValue(':content', $data['content']);
        $stmt->bindValue(':cover', $data['cover'] ?? '');
        $stmt->bindValue(':category_id', (int)($data['category_id'] ?? 0));
        $stmt->bindValue(':status', $data['status'] ?? 'active');
        $stmt->bindValue(':seo_title', $data['seo_title'] ?? '');
        $stmt->bindValue(':seo_keywords', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seo_description', $data['seo_description'] ?? '');
        $stmt->bindValue(':created_at', gmdate('c'));
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data, ?string $type = 'post'): void {
        $query = "UPDATE posts SET
            title = :title,
            slug = :slug,
            summary = :summary,
            content = :content,
            cover = :cover,
            category_id = :category_id,
            status = :status,
            seo_title = :seo_title,
            seo_keywords = :seo_keywords,
            seo_description = :seo_description,
            updated_at = :updated_at
            WHERE id = :id";
        if ($type !== null) {
            $query .= " AND post_type = :post_type";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':slug', $data['slug']);
        $stmt->bindValue(':summary', $data['summary']);
        $stmt->bindValue(':content', $data['content']);
        $stmt->bindValue(':cover', $data['cover'] ?? '');
        $stmt->bindValue(':category_id', (int)($data['category_id'] ?? 0));
        $stmt->bindValue(':status', $data['status'] ?? 'active');
        $stmt->bindValue(':seo_title', $data['seo_title'] ?? '');
        $stmt->bindValue(':seo_keywords', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seo_description', $data['seo_description'] ?? '');
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->bindValue(':id', $id);
        if ($type !== null) {
            $stmt->bindValue(':post_type', $this->normalizeType($type));
        }
        $stmt->execute();
    }

    public function getBySlug(string $slug, string $type = 'post'): ?array {
        return $this->fetchOne("SELECT posts.*, product_categories.name AS category_name
            FROM posts
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.slug = :slug AND posts.post_type = :post_type", [
            ':slug' => $slug,
            ':post_type' => $this->normalizeType($type),
        ]);
    }

    public function delete(int $id, ?string $type = 'post'): void {
        $query = "DELETE FROM posts WHERE id = :id";
        if ($type !== null) {
            $query .= " AND post_type = :post_type";
        }
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $id);
        if ($type !== null) {
            $stmt->bindValue(':post_type', $this->normalizeType($type));
        }
        $stmt->execute();
    }

    public function getByCategory(int $categoryId, int $limit = 0, string $type = 'post'): array {
        $query = "SELECT posts.*, product_categories.name AS category_name
            FROM posts
            LEFT JOIN product_categories ON product_categories.id = posts.category_id AND product_categories.type = 'post'
            WHERE posts.category_id = :category_id AND posts.status = 'active' AND posts.post_type = :post_type
            ORDER BY posts.id DESC";
        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        return $this->fetchAll($query, [
            ':category_id' => $categoryId,
            ':post_type' => $this->normalizeType($type),
        ]);
    }

    private function normalizeType(string $type): string {
        $allowed = ['post', 'case', 'page'];
        return in_array($type, $allowed, true) ? $type : 'post';
    }
}
