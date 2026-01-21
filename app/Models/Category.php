<?php
declare(strict_types=1);

namespace App\Models;

class Category extends BaseModel {
    /**
     * 获取所有分类（按类型）
     */
    public function getAll(string $type = 'product'): array {
        return $this->fetchAll(
            "SELECT * FROM product_categories WHERE type = :type ORDER BY sort_order ASC, id ASC",
            [':type' => $type]
        );
    }

    /**
     * 获取所有分类（不区分类型）
     */
    public function getAllCategories(): array {
        return $this->fetchAll("SELECT * FROM product_categories ORDER BY type ASC, sort_order ASC, id ASC");
    }

    /**
     * 按ID获取分类
     */
    public function getById(int $id): ?array {
        return $this->fetchOne("SELECT * FROM product_categories WHERE id = :id", [':id' => $id]);
    }

    /**
     * 获取子分类
     */
    public function getChildren(int $parentId, string $type = ''): array {
        if ($type) {
            return $this->fetchAll(
                "SELECT * FROM product_categories WHERE parent_id = :pid AND type = :type ORDER BY sort_order ASC, id ASC",
                [':pid' => $parentId, ':type' => $type]
            );
        }
        return $this->fetchAll(
            "SELECT * FROM product_categories WHERE parent_id = :pid ORDER BY sort_order ASC, id ASC",
            [':pid' => $parentId]
        );
    }

    /**
     * 构建树形结构
     */
    public function getTree(string $type = 'product'): array {
        $all = $this->getAll($type);
        return $this->buildTree($all, 0);
    }

    /**
     * 递归构建树
     */
    private function buildTree(array $items, int $parentId = 0, int $level = 0): array {
        $tree = [];
        foreach ($items as $item) {
            if ((int)$item['parent_id'] === $parentId) {
                $item['level'] = $level;
                $item['children'] = $this->buildTree($items, (int)$item['id'], $level + 1);
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 获取平铺的带层级的分类列表（用于下拉选择）
     */
    public function getFlatTree(string $type = 'product'): array {
        $tree = $this->getTree($type);
        return $this->flattenTree($tree);
    }

    /**
     * 递归平铺树
     */
    private function flattenTree(array $tree, string $prefix = ''): array {
        $result = [];
        foreach ($tree as $item) {
            $item['display_name'] = $prefix . $item['name'];
            $children = $item['children'] ?? [];
            unset($item['children']);
            $result[] = $item;
            if (!empty($children)) {
                $result = array_merge($result, $this->flattenTree($children, $prefix . '── '));
            }
        }
        return $result;
    }

    /**
     * 创建分类
     */
    public function create(string $name, string $slug, string $type = 'product', int $parentId = 0, string $description = ''): int {
        $stmt = $this->db->prepare("INSERT INTO product_categories (name, slug, type, parent_id, description, sort_order, created_at, updated_at) VALUES (:n, :s, :t, :p, :d, :so, :ca, :ua)");
        $stmt->bindValue(':n', $name);
        $stmt->bindValue(':s', $slug);
        $stmt->bindValue(':t', $type);
        $stmt->bindValue(':p', $parentId);
        $stmt->bindValue(':d', $description);
        $stmt->bindValue(':so', 0);
        $stmt->bindValue(':ca', gmdate('c'));
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    /**
     * 更新分类
     */
    public function update(int $id, string $name, string $slug, int $parentId = 0, string $description = ''): void {
        // 防止将分类设为自己的子分类
        if ($parentId === $id) {
            $parentId = 0;
        }
        
        // 检查是否会造成循环引用
        if ($parentId > 0 && $this->isDescendant($parentId, $id)) {
            $parentId = 0;
        }

        $stmt = $this->db->prepare("UPDATE product_categories SET name = :n, slug = :s, parent_id = :p, description = :d, updated_at = :ua WHERE id = :id");
        $stmt->bindValue(':n', $name);
        $stmt->bindValue(':s', $slug);
        $stmt->bindValue(':p', $parentId);
        $stmt->bindValue(':d', $description);
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    /**
     * 检查 childId 是否是 parentId 的后代
     */
    private function isDescendant(int $childId, int $ancestorId): bool {
        $current = $this->getById($childId);
        while ($current) {
            if ((int)$current['parent_id'] === $ancestorId) {
                return true;
            }
            if ((int)$current['parent_id'] === 0) {
                break;
            }
            $current = $this->getById((int)$current['parent_id']);
        }
        return false;
    }

    /**
     * 删除分类
     */
    public function delete(int $id): void {
        $category = $this->getById($id);
        if (!$category) return;

        $type = $category['type'] ?? 'product';

        // 将子分类提升到父级
        $parentId = (int)($category['parent_id'] ?? 0);
        $stmt = $this->db->prepare("UPDATE product_categories SET parent_id = :parent WHERE parent_id = :id");
        $stmt->bindValue(':parent', $parentId);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        // 清除关联
        if ($type === 'product') {
            $this->db->exec("UPDATE products SET category_id = 0 WHERE category_id = $id");
        } elseif ($type === 'post') {
            $this->db->exec("UPDATE posts SET category_id = 0 WHERE category_id = $id");
        }

        // 删除分类
        $stmt = $this->db->prepare("DELETE FROM product_categories WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    /**
     * 更新排序
     */
    public function updateSort(int $id, int $sortOrder): void {
        $stmt = $this->db->prepare("UPDATE product_categories SET sort_order = :so, updated_at = :ua WHERE id = :id");
        $stmt->bindValue(':so', $sortOrder);
        $stmt->bindValue(':ua', gmdate('c'));
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    }

    /**
     * 获取分类的完整路径
     */
    public function getBreadcrumb(int $id): array {
        $path = [];
        $current = $this->getById($id);
        while ($current) {
            array_unshift($path, $current);
            if ((int)$current['parent_id'] === 0) {
                break;
            }
            $current = $this->getById((int)$current['parent_id']);
        }
        return $path;
    }

    /**
     * 统计分类下的项目数量
     */
    public function countItems(int $categoryId, string $type = 'product'): int {
        $table = $type === 'product' ? 'products' : 'posts';
        return (int)$this->db->querySingle("SELECT COUNT(*) FROM $table WHERE category_id = $categoryId");
    }
}
