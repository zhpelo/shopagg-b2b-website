<?php
declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel {
    public function getList(int $limit = 0, bool $activeOnly = false): array {
        $query = "SELECT products.*, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id";
        if ($activeOnly) {
            $query .= " WHERE products.status = 'active'";
        }
        $query .= " ORDER BY products.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        $items = $this->fetchAll($query);
        foreach ($items as &$item) {
            $images = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $images[0] ?? '';
        }
        return $items;
    }

    public function getById(int $id): ?array {
        $item = $this->fetchOne("SELECT * FROM products WHERE id = :id", [':id' => $id]);
        if ($item) {
            $item['images'] = json_decode((string)($item['images_json'] ?? '[]'), true);
        }
        return $item;
    }

    public function getBySlug(string $slug): ?array {
        $item = $this->fetchOne("SELECT products.*, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.slug = :s", [':s' => $slug]);
        if ($item) {
            $item['images'] = json_decode((string)($item['images_json'] ?? '[]'), true);
        }
        return $item;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO products (title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, banner_image, seo_title, seo_keywords, seo_description, created_at, updated_at)
            VALUES (:t, :s, :sum, :c, :cid, :st, :pt, :v, :tags, :imgs, :bi, :seot, :seok, :seod, :ca, :ua)");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)$data['category_id']);
        $stmt->bindValue(':st', $data['status'] ?? 'active');
        $stmt->bindValue(':pt', $data['product_type'] ?? '');
        $stmt->bindValue(':v', $data['vendor'] ?? '');
        $stmt->bindValue(':tags', $data['tags'] ?? '');
        $stmt->bindValue(':imgs', $data['images_json'] ?? '[]');
        $stmt->bindValue(':bi', $data['banner_image'] ?? '');
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE products SET title=:t, slug=:s, summary=:sum, content=:c, category_id=:cid, status=:st, product_type=:pt, vendor=:v, tags=:tags, images_json=:imgs, banner_image=:bi, seo_title=:seot, seo_keywords=:seok, seo_description=:seod, updated_at=:ua WHERE id=:id");
        $stmt->bindValue(':t', $data['title']);
        $stmt->bindValue(':s', $data['slug']);
        $stmt->bindValue(':sum', $data['summary']);
        $stmt->bindValue(':c', $data['content']);
        $stmt->bindValue(':cid', (int)$data['category_id']);
        $stmt->bindValue(':st', $data['status'] ?? 'active');
        $stmt->bindValue(':pt', $data['product_type'] ?? '');
        $stmt->bindValue(':v', $data['vendor'] ?? '');
        $stmt->bindValue(':tags', $data['tags'] ?? '');
        $stmt->bindValue(':imgs', $data['images_json'] ?? '[]');
        $stmt->bindValue(':bi', $data['banner_image'] ?? '');
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
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
        return $this->fetchAll("SELECT * FROM product_images WHERE product_id = :pid ORDER BY sort ASC, id ASC", [':pid' => $productId]);
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

    public function getByCategory(int $categoryId, int $limit = 0): array {
        $query = "SELECT products.*, product_categories.name AS category_name
            FROM products
            LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.category_id = :cid AND products.status = 'active'
            ORDER BY products.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        $items = $this->fetchAll($query, [':cid' => $categoryId]);
        foreach ($items as &$item) {
            $images = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $images[0] ?? '';
        }
        return $items;
    }

    public function getLatest(int $limit = 6): array {
        $query = "SELECT products.*, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.status = 'active'
            ORDER BY products.id DESC
            LIMIT :limit";
        $items = $this->fetchAll($query, [':limit' => $limit]);
        foreach ($items as &$item) {
            $images = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $images[0] ?? '';
        }
        return $items;
    }

    public function getFeatured(int $limit = 6): array {
        $query = "SELECT products.*, product_categories.name AS category_name
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.status = 'active' AND products.banner_image IS NOT NULL AND products.banner_image != ''
            ORDER BY products.id DESC
            LIMIT :limit";
        $items = $this->fetchAll($query, [':limit' => $limit]);
        foreach ($items as &$item) {
            $images = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $images[0] ?? '';
        }
        return $items;
    }
}
