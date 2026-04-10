<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use SQLite3;

/**
 * 菜单模型
 * 
 * 管理菜单和菜单项的增删改查，支持多级菜单
 */
class Menu {
    private SQLite3 $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 获取所有菜单
     */
    public function getAll(): array {
        $result = $this->db->query("SELECT * FROM menus ORDER BY sort_order ASC, id DESC");
        $menus = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $menus[] = $row;
        }
        return $menus;
    }

    /**
     * 根据ID获取菜单
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    /**
     * 根据slug获取菜单
     */
    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM menus WHERE slug = :slug");
        $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    /**
     * 获取菜单及其所有菜单项（树形结构）
     */
    public function getWithItems(int $id, bool $tree = true): ?array {
        $menu = $this->getById($id);
        if (!$menu) {
            return null;
        }
        
        $items = $this->getItems($id);
        
        if ($tree) {
            $menu['items'] = $this->buildTree($items);
        } else {
            $menu['items'] = $items;
        }
        
        return $menu;
    }

    /**
     * 根据slug获取菜单及其菜单项
     */
    public function getBySlugWithItems(string $slug, bool $tree = true): ?array {
        $menu = $this->getBySlug($slug);
        if (!$menu) {
            return null;
        }
        
        $items = $this->getItems((int)$menu['id']);
        
        if ($tree) {
            $menu['items'] = $this->buildTree($items);
        } else {
            $menu['items'] = $items;
        }
        
        return $menu;
    }

    /**
     * 获取菜单的所有菜单项（扁平结构）
     */
    public function getItems(int $menuId): array {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE menu_id = :menu_id AND status = 'active' ORDER BY sort_order ASC, id ASC");
        $stmt->bindValue(':menu_id', $menuId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        }
        return $items;
    }

    /**
     * 构建菜单树形结构
     */
    private function buildTree(array $items, int $parentId = 0): array {
        $tree = [];
        foreach ($items as $item) {
            if ((int)$item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, (int)$item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 创建菜单
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("INSERT INTO menus (name, slug, description, location, status, sort_order) VALUES (:name, :slug, :description, :location, :status, :sort_order)");
        $stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $data['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $data['description'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':location', $data['location'] ?? 'header', SQLITE3_TEXT);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    /**
     * 更新菜单
     */
    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare("UPDATE menus SET name = :name, slug = :slug, description = :description, location = :location, status = :status, sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
        $stmt->bindValue(':slug', $data['slug'], SQLITE3_TEXT);
        $stmt->bindValue(':description', $data['description'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':location', $data['location'] ?? 'header', SQLITE3_TEXT);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 删除菜单（关联的菜单项会自动删除）
     */
    public function delete(int $id): void {
        $stmt = $this->db->prepare("DELETE FROM menus WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 添加菜单项
     */
    public function addItem(int $menuId, array $data): int {
        $stmt = $this->db->prepare("INSERT INTO menu_items (menu_id, parent_id, title, url, target, css_class, sort_order, status) VALUES (:menu_id, :parent_id, :title, :url, :target, :css_class, :sort_order, :status)");
        $stmt->bindValue(':menu_id', $menuId, SQLITE3_INTEGER);
        $stmt->bindValue(':parent_id', $data['parent_id'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':title', $data['title'], SQLITE3_TEXT);
        $stmt->bindValue(':url', $data['url'], SQLITE3_TEXT);
        $stmt->bindValue(':target', $data['target'] ?? '_self', SQLITE3_TEXT);
        $stmt->bindValue(':css_class', $data['css_class'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    /**
     * 更新菜单项
     */
    public function updateItem(int $itemId, array $data): void {
        $stmt = $this->db->prepare("UPDATE menu_items SET parent_id = :parent_id, title = :title, url = :url, target = :target, css_class = :css_class, sort_order = :sort_order, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $stmt->bindValue(':parent_id', $data['parent_id'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':title', $data['title'], SQLITE3_TEXT);
        $stmt->bindValue(':url', $data['url'], SQLITE3_TEXT);
        $stmt->bindValue(':target', $data['target'] ?? '_self', SQLITE3_TEXT);
        $stmt->bindValue(':css_class', $data['css_class'] ?? '', SQLITE3_TEXT);
        $stmt->bindValue(':sort_order', $data['sort_order'] ?? 0, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $data['status'] ?? 'active', SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * 删除菜单项
     */
    public function deleteItem(int $itemId): void {
        // 先删除子菜单项
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE parent_id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $stmt->execute();
        
        // 删除当前菜单项
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 删除菜单的所有菜单项
     */
    public function deleteAllItems(int $menuId): void {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE menu_id = :menu_id");
        $stmt->bindValue(':menu_id', $menuId, SQLITE3_INTEGER);
        $stmt->execute();
    }

    /**
     * 根据ID获取单个菜单项
     */
    public function getItemById(int $itemId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = :id");
        $stmt->bindValue(':id', $itemId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ?: null;
    }

    /**
     * 获取可作为父级的菜单项（排除自身及其子项）
     */
    public function getPotentialParents(int $menuId, ?int $excludeItemId = null): array {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE menu_id = :menu_id AND status = 'active' ORDER BY sort_order ASC, id ASC");
        $stmt->bindValue(':menu_id', $menuId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($excludeItemId && (int)$row['id'] === $excludeItemId) {
                continue;
            }
            $items[] = $row;
        }
        return $items;
    }
}
