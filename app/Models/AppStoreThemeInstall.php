<?php
declare(strict_types=1);

namespace App\Models;

class AppStoreThemeInstall extends BaseModel {
    public function allIndexedByResourceId(): array {
        $rows = $this->fetchAll('SELECT * FROM app_store_theme_installs ORDER BY updated_at DESC');
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['resource_id']] = $row;
        }
        return $indexed;
    }

    public function saveInstall(array $data): void {
        $now = gmdate('c');
        $stmt = $this->db->prepare(
            'INSERT INTO app_store_theme_installs (
                resource_id, resource_slug, theme_slug, name, version, bound_domain, installed_at, updated_at
            ) VALUES (
                :resource_id, :resource_slug, :theme_slug, :name, :version, :bound_domain, :installed_at, :updated_at
            )
            ON CONFLICT(resource_id) DO UPDATE SET
                resource_slug = excluded.resource_slug,
                theme_slug = excluded.theme_slug,
                name = excluded.name,
                version = excluded.version,
                bound_domain = excluded.bound_domain,
                updated_at = excluded.updated_at'
        );

        $stmt->bindValue(':resource_id', (int)($data['resource_id'] ?? 0), SQLITE3_INTEGER);
        $stmt->bindValue(':resource_slug', (string)($data['resource_slug'] ?? ''), SQLITE3_TEXT);
        $stmt->bindValue(':theme_slug', (string)($data['theme_slug'] ?? ''), SQLITE3_TEXT);
        $stmt->bindValue(':name', (string)($data['name'] ?? ''), SQLITE3_TEXT);
        $stmt->bindValue(':version', (string)($data['version'] ?? ''), SQLITE3_TEXT);
        $stmt->bindValue(':bound_domain', (string)($data['bound_domain'] ?? ''), SQLITE3_TEXT);
        $stmt->bindValue(':installed_at', (string)($data['installed_at'] ?? $now), SQLITE3_TEXT);
        $stmt->bindValue(':updated_at', $now, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function deleteByThemeSlug(string $themeSlug): void {
        $stmt = $this->db->prepare('DELETE FROM app_store_theme_installs WHERE theme_slug = :theme_slug');
        $stmt->bindValue(':theme_slug', $themeSlug, SQLITE3_TEXT);
        $stmt->execute();
    }
}
