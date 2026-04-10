<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use SQLite3;

/**
 * 轮播图模型
 * 
 * 管理轮播图区块和轮播图片的增删改查
 */
class Slider {
    private SQLite3 $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 获取所有轮播图区块
     */
    public function getAll(): array {
        $result = $this->db->query("SELECT * FROM sliders ORDER BY sort_order ASC, id DESC");
        $sliders = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $sliders[] = $row;
        }
        return $sliders;
    }

    /**
     * 根据ID获取轮播图区块
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sliders WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    /**
     * 根据slug获取轮播图区块
     */
    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM sliders WHERE slug = :slug");
        $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    /**
     * 获取轮播图区块及其图片项
     */
    public function getWithItems(int $id): ?array {
        $slider = $this->getById($id);
        if (!$slider) {
            return null;
        }
        $slider['items'] = $this->getItems($id);
        return $slider;
    }

    /**
     * 根据slug获取轮播图区块及其图片项
     */
    public function getBySlugWithItems(string $slug): ?array {
        $slider = $this->getBySlug($slug);
        if (!$slider) {
            return null;
        }
        $slider['items'] = $this->getItems((int)$slider['id']);
        return $slider;
    }

    /**
     * 获取轮播图的所有图片项
     */
    public function getItems(int $sliderId): array {
        $stmt = $this->db->prepare("SELECT * FROM slider_items WHERE slider_id = :slider_id AND status = 'active' ORDER BY sort_order ASC, id ASC");
        $stmt->bindValue(':slider_id', $sliderId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        }
        return $items;
    }

    /**
     * 创建轮播图区块
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO sliders (name, slug, description, status, sort_order) VALUES (:name, :slug, :description, :status, :sort_order)");
        $stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $data['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $data['description'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    /**
     * 更新轮播图区块
     */
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE sliders SET name = :name, slug = :slug, description = :description, status = :status, sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $data['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $data['description'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 删除轮播图区块（关联的图片项会自动删除）
     */
    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM sliders WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 添加轮播图片项
     */
    public function addItem(int $sliderId, array $data): int {
        $stmt = $this->db->prepare("INSERT INTO slider_items (slider_id, image, title, subtitle, link_url, link_text, sort_order, status) VALUES (:slider_id, :image, :title, :subtitle, :link_url, :link_text, :sort_order, :status)");
        $stmt->bindValue(':slider_id', $sliderId, SQLITE3_INTEGER);
        $stmt->bindValue(':image', $data['image'], SQLITE3_TEXT);
        $stmt->bindValue(':title', $data['title'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':subtitle', $data['subtitle'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':link_url', $data['link_url'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':link_text', $data['link_text'] ?? 'View Details', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    /**
     * 更新轮播图片项
     */
    public function updateItem(int $itemId, array $data): void {
        $stmt = $this->db->prepare("UPDATE slider_items SET image = :image, title = :title, subtitle = :subtitle, link_url = :link_url, link_text = :link_text, sort_order = :sort_order, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $stmt->bindValue(':image', $data['image'], SQLITE3_TEXT);
        $stmt->bindValue(':title', $data['title'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':subtitle', $data['subtitle'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':link_url', $data['link_url'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':link_text', $data['link_text'] ?? 'View Details', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * 删除轮播图片项
     */
    public function deleteItem(int $itemId): void {
        $stmt = $this->db->prepare("DELETE FROM slider_items WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 删除轮播图的所有图片项
     */
    public function deleteAllItems(int $sliderId): void {
        $stmt = $this->db->prepare("DELETE FROM slider_items WHERE slider_id = :slider_id");
        $stmt->bindValue(':slider_id', $sliderId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 根据ID获取单张轮播图片
     */
    public function getItemById(int $itemId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM slider_items WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }
}
