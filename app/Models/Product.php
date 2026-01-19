<?php
declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel {
    public function getList(int $limit = 0): array {
        $query = "SELECT products.*, product_categories.name AS category_name,
            (SELECT url FROM product_images WHERE product_id = products.id ORDER BY sort ASC, id ASC LIMIT 1) AS cover
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            ORDER BY products.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        return $this->fetchAll($query);
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM products WHERE id = :id", [':id' => $id]);
    }

    public function getBySlug(string $slug): ?array {
        return $this->fetchOne("SELECT products.*, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.slug = :s", [':s' => $slug]);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO products (title, slug, summary, content, category_id, created_at, updated_at) 
            VALUES (:t, :s, :sum, :c, :cid, :ca, :ua)");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)$data['category_id']);
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE products SET title=:t, slug=:s, summary=:sum, content=:c, category_id=:cid, updated_at=:ua WHERE id=:id");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)$data['category_id']);
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function getImages(int $productId): array {
        return $this->fetchAll("SELECT * FROM product_images WHERE productId = :pid ORDER BY sort ASC, id ASC", [':pid' => $productId]);
    }

    public function addImage(int $productId, string $url, int $sort = 0): void {
        $stmt = $this->db->prepare("INSERT INTO product_images (product_id, url, sort, created_at) VALUES (:pid, :url, :s, :t)");
        $stmt->bindValue(':pid', $productId);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':s', $sort);
        $stmt->bindValue(':t', gmdate('c'));
        $stmt->execute();
    }

    public function deleteImage(int $imageId, int $productId): void {
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id AND product_id = :pid");
        $stmt->bindValue(':id', $imageId);
        $stmt->bindValue(':pid', $productId);
        $stmt->execute();
    }

    public function getPrices(int $productId): array {
        return $this->fetchAll("SELECT * FROM product_prices WHERE product_id = :pid ORDER BY min_qty ASC, id ASC", [':pid' => $productId]);
    }

    public function savePrices(int $productId, array $tiers): void {
        $this->db->exec("DELETE FROM product_prices WHERE product_id = $productId");
        foreach ($tiers as $tier) {
            $stmt = $this->db->prepare("INSERT INTO product_prices (product_id, min_qty, max_qty, price, currency, created_at)
                VALUES (:pid, :min, :max, :p, :c, :t)");
            $stmt->bindValue(':pid', $productId);
            $stmt->bindValue(':min', (int)$tier['min_qty']);
            $stmt->bindValue(':max', $tier['max_qty'] ? (int)$tier['max_qty'] : null);
            $stmt->bindValue(':p', (float)$tier['price']);
            $stmt->bindValue(':c', $tier['currency']);
            $stmt->bindValue(':t', gmdate('c'));
            $stmt->execute();
        }
    }
}
