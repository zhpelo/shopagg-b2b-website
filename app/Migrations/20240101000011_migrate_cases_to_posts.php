<?php
declare(strict_types=1);

/**
 * 迁移: 将 cases 表数据迁移到 posts 表
 * 版本: 20240101000011
 */


return new class {
    public function up(SQLite3 $db): void {
        $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'cases'");
        if (!$tableExists) {
            return;
        }
        
        $result = $db->query("SELECT * FROM cases ORDER BY id ASC");
        if (!$result) {
            return;
        }
        
        while ($case = $result->fetchArray(SQLITE3_ASSOC)) {
            $existing = $db->prepare("SELECT id FROM posts WHERE post_type = :post_type AND slug = :slug LIMIT 1");
            $existing->bindValue(':post_type', 'case', SQLITE3_TEXT);
            $existing->bindValue(':slug', (string)($case['slug'] ?? ''), SQLITE3_TEXT);
            $existingRow = $existing->execute()?->fetchArray(SQLITE3_ASSOC);
            if ($existingRow) {
                continue;
            }
            
            $slug = $this->resolveSlug($db, (string)($case['slug'] ?? ''), (string)($case['title'] ?? ''), (int)($case['id'] ?? 0));
            $stmt = $db->prepare("
                INSERT INTO posts (
                    title, slug, post_type, summary, content, cover, category_id, status,
                    seo_title, seo_keywords, seo_description, created_at, updated_at
                ) VALUES (
                    :title, :slug, :post_type, :summary, :content, :cover, 0, 'active',
                    :seo_title, :seo_keywords, :seo_description, :created_at, :updated_at
                )
            ");
            $stmt->bindValue(':title', (string)($case['title'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
            $stmt->bindValue(':post_type', 'case', SQLITE3_TEXT);
            $stmt->bindValue(':summary', (string)($case['summary'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':content', (string)($case['content'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':cover', (string)($case['cover'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_title', (string)($case['seo_title'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_keywords', (string)($case['seo_keywords'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':seo_description', (string)($case['seo_description'] ?? ''), SQLITE3_TEXT);
            $stmt->bindValue(':created_at', (string)($case['created_at'] ?? gmdate('c')), SQLITE3_TEXT);
            $stmt->bindValue(':updated_at', (string)($case['updated_at'] ?? gmdate('c')), SQLITE3_TEXT);
            $stmt->execute();
        }
        
        // 迁移完成后可选择删除旧表
        // $db->exec('DROP TABLE IF EXISTS cases');
    }
    
    public function down(SQLite3 $db): void {
        // 回滚需要从 posts 表删除 case 类型的数据
        // 但通常不建议回滚数据迁移
    }
    
    private function resolveSlug(SQLite3 $db, string $slug, string $title, int $caseId): string {
        $baseSlug = trim($slug);
        if ($baseSlug === '') {
            $baseSlug = $this->slugify($title !== '' ? $title : 'case-' . $caseId);
        }
        
        $candidate = $baseSlug;
        $suffix = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM posts WHERE slug = :slug LIMIT 1");
            $stmt->bindValue(':slug', $candidate, SQLITE3_TEXT);
            $row = $stmt->execute()?->fetchArray(SQLITE3_ASSOC);
            if (!$row) {
                return $candidate;
            }
            $candidate = $baseSlug . '-case' . ($suffix > 1 ? '-' . $suffix : '');
            $suffix++;
        }
    }
    
    private function slugify(string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');
        return $value !== '' ? $value : 'case';
    }
};
