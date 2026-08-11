<?php
declare(strict_types=1);

return new class {
    public function up(\SQLite3 $db): void {
        $columns = [];
        $result = $db->query('PRAGMA table_info(plugins)');
        while ($result && ($row = $result->fetchArray(SQLITE3_ASSOC))) {
            $columns[(string)$row['name']] = true;
        }

        if (!isset($columns['active_manifest_json'])) {
            if (!$db->exec('ALTER TABLE plugins ADD COLUMN active_manifest_json TEXT')) {
                throw new RuntimeException('无法升级插件表：' . $db->lastErrorMsg());
            }
        }
    }

    public function down(\SQLite3 $db): void {
        // 保留该兼容字段，避免回滚时破坏已启用插件的 Manifest 快照。
    }
};
