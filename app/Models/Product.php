<?php
declare(strict_types=1);

namespace App\Models;

class Product extends BaseModel {
    private const ADMIN_SORTS = [
        'latest' => 'products.id DESC',
        'oldest' => 'products.id ASC',
        'title_asc' => 'products.title COLLATE NOCASE ASC, products.id DESC',
        'title_desc' => 'products.title COLLATE NOCASE DESC, products.id DESC',
        'updated_desc' => 'products.updated_at DESC, products.id DESC',
        'updated_asc' => 'products.updated_at ASC, products.id ASC',
        'status_asc' => 'products.status ASC, products.id DESC',
        'category_asc' => 'product_categories.name COLLATE NOCASE ASC, products.id DESC',
        'deleted_desc' => 'products.deleted_at DESC, products.id DESC',
    ];

    public function getList(int $limit = 0, bool $activeOnly = false): array {
        $query = "SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id";
        $where = ['products.deleted_at IS NULL'];
        if ($activeOnly) {
            $where[] = "products.status = 'active'";
        }
        $query .= " WHERE " . implode(' AND ', $where);
        $query .= " ORDER BY products.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        $items = $this->fetchAll($query);
        return $this->withCovers($items);
    }

    public function getAdminList(array $filters = []): array {
        [$where, $params] = $this->buildAdminWhere($filters);
        $sort = (string)($filters['sort'] ?? '');
        if (!isset(self::ADMIN_SORTS[$sort])) {
            $sort = !empty($filters['trash']) ? 'deleted_desc' : 'latest';
        }

        $query = "SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products
            LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY " . self::ADMIN_SORTS[$sort];

        return $this->withCovers($this->fetchAll($query, $params));
    }

    public function getAdminCounts(): array {
        $row = $this->db->querySingle(
            "SELECT
                COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) AS all_products,
                COUNT(CASE WHEN deleted_at IS NULL AND status = 'active' THEN 1 END) AS active_products,
                COUNT(CASE WHEN deleted_at IS NULL AND status IN ('inactive', 'archived') THEN 1 END) AS inactive_products,
                COUNT(CASE WHEN deleted_at IS NULL AND status = 'draft' THEN 1 END) AS draft_products,
                COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) AS trash_products
            FROM products",
            true
        );

        return [
            'all' => (int)($row['all_products'] ?? 0),
            'active' => (int)($row['active_products'] ?? 0),
            'inactive' => (int)($row['inactive_products'] ?? 0),
            'draft' => (int)($row['draft_products'] ?? 0),
            'trash' => (int)($row['trash_products'] ?? 0),
        ];
    }

    public function getById(int $id): ?array {
        $item = $this->fetchOne("SELECT * FROM products WHERE id = :id", [':id' => $id]);
        if ($item) {
            $item['images'] = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $item['images'][0] ?? '';
        }
        return $item;
    }

    public function getBySlug(string $slug): ?array {
        $item = $this->fetchOne("SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.slug = :s AND products.deleted_at IS NULL", [':s' => $slug]);
        if ($item) {
            $item['images'] = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = $item['images'][0] ?? '';
        }
        return $item;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO products (title, slug, summary, content, category_id, status, product_type, vendor, tags, images_json, seo_title, seo_keywords, seo_description, created_at, updated_at)
            VALUES (:t, :s, :sum, :c, :cid, :st, :pt, :v, :tags, :imgs, :seot, :seok, :seod, :ca, :ua)");
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
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE products SET title=:t, slug=:s, summary=:sum, content=:c, category_id=:cid, status=:st, product_type=:pt, vendor=:v, tags=:tags, images_json=:imgs, seo_title=:seot, seo_keywords=:seok, seo_description=:seod, updated_at=:ua WHERE id=:id");
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
        $stmt->bindValue(':seot', $data['seo_title'] ?? '');
        $stmt->bindValue(':seok', $data['seo_keywords'] ?? '');
        $stmt->bindValue(':seod', $data['seo_description'] ?? '');
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function delete(int $id): void {
        $this->softDelete($id);
    }

    public function softDelete(int $id): void {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = :deleted_at, updated_at = :updated_at WHERE id = :id AND deleted_at IS NULL");
        $now = gmdate('c');
        $stmt->bindValue(':deleted_at', $now);
        $stmt->bindValue(':updated_at', $now);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function restore(int $id): void {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = NULL, updated_at = :updated_at WHERE id = :id");
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function permanentDelete(int $id): void {
        $trashed = $this->fetchOne("SELECT id FROM products WHERE id = :id AND deleted_at IS NOT NULL", [':id' => $id]);
        if (!$trashed) {
            return;
        }

        $priceStmt = $this->db->prepare("DELETE FROM product_prices WHERE product_id = :id");
        $priceStmt->bindValue(':id', $id);
        $priceStmt->execute();

        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function updateStatus(int $id, string $status): void {
        if (!in_array($status, ['active', 'inactive', 'draft'], true)) {
            return;
        }

        $stmt = $this->db->prepare("UPDATE products SET status = :status, updated_at = :updated_at WHERE id = :id AND deleted_at IS NULL");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':updated_at', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    public function bulkUpdateStatus(array $ids, string $status): void {
        foreach ($this->normalizeIds($ids) as $id) {
            $this->updateStatus($id, $status);
        }
    }

    public function bulkSoftDelete(array $ids): void {
        foreach ($this->normalizeIds($ids) as $id) {
            $this->softDelete($id);
        }
    }

    public function bulkRestore(array $ids): void {
        foreach ($this->normalizeIds($ids) as $id) {
            $this->restore($id);
        }
    }

    public function bulkPermanentDelete(array $ids): void {
        foreach ($this->normalizeIds($ids) as $id) {
            $this->permanentDelete($id);
        }
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
        $query = "SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products
            LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.category_id = :cid AND products.status = 'active' AND products.deleted_at IS NULL
            ORDER BY products.id DESC";
        if ($limit > 0) $query .= " LIMIT $limit";
        $items = $this->fetchAll($query, [':cid' => $categoryId]);
        return $this->withCovers($items);
    }

    public function getLatest(int $limit = 6): array {
        $query = "SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.status = 'active' AND products.deleted_at IS NULL
            ORDER BY products.id DESC
            LIMIT :limit";
        $items = $this->fetchAll($query, [':limit' => $limit]);
        return $this->withCovers($items);
    }

    public function getFeatured(int $limit = 6): array {
        $query = "SELECT products.*, product_categories.name AS category_name, product_categories.slug AS category_slug
            FROM products LEFT JOIN product_categories ON product_categories.id = products.category_id
            WHERE products.status = 'active' AND products.deleted_at IS NULL AND products.images_json IS NOT NULL AND products.images_json != '' AND products.images_json != '[]'
            ORDER BY products.id DESC
            LIMIT :limit";
        $items = $this->fetchAll($query, [':limit' => $limit]);
        return $this->withCovers($items);
    }

    private function buildAdminWhere(array $filters): array {
        $where = [];
        $params = [];

        if (!empty($filters['trash'])) {
            $where[] = 'products.deleted_at IS NOT NULL';
        } else {
            $where[] = 'products.deleted_at IS NULL';
        }

        $search = trim((string)($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = "(products.title LIKE :q OR products.slug LIKE :q OR products.summary LIKE :q OR products.product_type LIKE :q OR products.vendor LIKE :q OR products.tags LIKE :q)";
            $params[':q'] = '%' . $search . '%';
        }

        $status = (string)($filters['status'] ?? '');
        if (!empty($filters['trash'])) {
            $status = '';
        }
        if ($status === 'inactive') {
            $where[] = "products.status IN ('inactive', 'archived')";
        } elseif (in_array($status, ['active', 'draft', 'archived'], true)) {
            $where[] = 'products.status = :status';
            $params[':status'] = $status;
        }

        $categoryId = (int)($filters['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 'products.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        return [$where, $params];
    }

    private function withCovers(array $items): array {
        foreach ($items as &$item) {
            $images = json_decode((string)($item['images_json'] ?? '[]'), true);
            $item['cover'] = is_array($images) ? (string)($images[0] ?? '') : '';
        }
        unset($item);
        return $items;
    }

    private function normalizeIds(array $ids): array {
        $normalized = [];
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $normalized[] = $id;
            }
        }
        return array_values(array_unique($normalized));
    }
}
