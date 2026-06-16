<?php
declare(strict_types=1);

/**
 * 迁移: 创建 App Store B2B 主题安装记录
 * 版本: 20260617000000
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS app_store_theme_installs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            resource_id INTEGER NOT NULL,
            resource_slug TEXT NOT NULL,
            theme_slug TEXT NOT NULL,
            name TEXT NOT NULL,
            version TEXT,
            bound_domain TEXT,
            installed_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )');

        $db->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_app_store_theme_installs_resource ON app_store_theme_installs(resource_id)');
        $db->exec('CREATE INDEX IF NOT EXISTS idx_app_store_theme_installs_theme_slug ON app_store_theme_installs(theme_slug)');

        $defaults = [
            'app_store_api_token' => '',
        ];

        foreach ($defaults as $key => $value) {
            $stmt = $db->prepare('INSERT OR IGNORE INTO settings (key, value) VALUES (:key, :value)');
            $stmt->bindValue(':key', $key, SQLITE3_TEXT);
            $stmt->bindValue(':value', $value, SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS app_store_theme_installs');
    }
};
