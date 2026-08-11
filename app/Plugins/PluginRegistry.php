<?php
declare(strict_types=1);

namespace App\Plugins;

use App\Core\Database;
use SQLite3;

final class PluginRegistry {
    private SQLite3 $db;

    public function __construct(?SQLite3 $db = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function all(): array {
        $result = $this->db->query('SELECT * FROM plugins ORDER BY name, plugin_id');
        $rows = [];
        while ($result && ($row = $result->fetchArray(SQLITE3_ASSOC))) {
            $row['manifest'] = json_decode((string)$row['manifest_json'], true) ?: [];
            $row['active_manifest'] = $row['active_manifest_json'] ? (json_decode((string)$row['active_manifest_json'], true) ?: []) : null;
            $rows[] = $row;
        }
        return $rows;
    }

    public function enabled(): array {
        return array_values(array_map(static function (array $row): array {
            if (is_array($row['active_manifest'] ?? null)) $row['manifest'] = $row['active_manifest'];
            return $row;
        }, array_filter($this->all(), static fn(array $row): bool => $row['status'] === 'enabled')));
    }

    public function find(string $pluginId): ?array {
        $stmt = $this->db->prepare('SELECT * FROM plugins WHERE plugin_id = :id LIMIT 1');
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;
        if (!$row) return null;
        $row['manifest'] = json_decode((string)$row['manifest_json'], true) ?: [];
        $row['active_manifest'] = $row['active_manifest_json'] ? (json_decode((string)$row['active_manifest_json'], true) ?: []) : null;
        return $row;
    }

    public function save(array $manifest, string $source = 'private', string $status = 'installed'): void {
        $now = gmdate('c');
        $stmt = $this->db->prepare("INSERT INTO plugins (
            plugin_id,name,vendor,description,installed_version,active_version,status,source,manifest_json,installed_at,updated_at
        ) VALUES (:id,:name,:vendor,:description,:version,NULL,:status,:source,:manifest,:now,:now)
        ON CONFLICT(plugin_id) DO UPDATE SET name=excluded.name,vendor=excluded.vendor,description=excluded.description,
            installed_version=excluded.installed_version,source=excluded.source,manifest_json=excluded.manifest_json,updated_at=excluded.updated_at");
        foreach (['id', 'name', 'vendor', 'description', 'version'] as $key) {
            $stmt->bindValue(':' . $key, (string)($manifest[$key] ?? ''), SQLITE3_TEXT);
        }
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':source', $source, SQLITE3_TEXT);
        $stmt->bindValue(':manifest', json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), SQLITE3_TEXT);
        $stmt->bindValue(':now', $now, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function setStatus(string $pluginId, string $status, ?string $activeVersion = null): void {
        $stmt = $this->db->prepare("UPDATE plugins SET status=:status, active_version=:version,
            active_manifest_json=CASE WHEN :status='enabled' THEN manifest_json ELSE active_manifest_json END,
            failure_count=0, last_error=NULL, updated_at=:now WHERE plugin_id=:id");
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':version', $activeVersion, $activeVersion === null ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function recordFailure(string $pluginId, \Throwable $error): int {
        $message = get_class($error) . ': ' . $error->getMessage();
        $stmt = $this->db->prepare('UPDATE plugins SET failure_count=failure_count+1,last_error=:error,updated_at=:now WHERE plugin_id=:id');
        $stmt->bindValue(':error', substr($message, 0, 4000), SQLITE3_TEXT);
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->execute();
        $this->log($pluginId, 'error', $message, ['file' => $error->getFile(), 'line' => $error->getLine()]);
        $row = $this->find($pluginId);
        $count = (int)($row['failure_count'] ?? 0);
        if ($count >= 3) {
            $stmt = $this->db->prepare("UPDATE plugins SET status='error',active_version=NULL WHERE plugin_id=:id");
            $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
            $stmt->execute();
            try { (new PluginCache())->rebuild($this->enabled()); } catch (\Throwable $cacheError) { error_log('[Plugin Cache] ' . $cacheError->getMessage()); }
        }
        return $count;
    }

    public function delete(string $pluginId): void {
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            foreach (['plugin_job_runs', 'plugin_jobs', 'plugin_logs', 'plugin_migrations', 'plugin_options'] as $table) {
                $stmt = $this->db->prepare("DELETE FROM {$table} WHERE plugin_id=:id");
                $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
                $stmt->execute();
            }
            $stmt = $this->db->prepare('DELETE FROM plugins WHERE plugin_id=:id');
            $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
            $stmt->execute();
            $this->db->exec('COMMIT');
        } catch (\Throwable $e) {
            $this->db->exec('ROLLBACK');
            throw $e;
        }
    }

    public function option(string $pluginId, string $key, mixed $default = null): mixed {
        $stmt = $this->db->prepare('SELECT option_value FROM plugin_options WHERE plugin_id=:id AND option_key=:key');
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result ? $result->fetchArray(SQLITE3_ASSOC) : false;
        return $row ? json_decode((string)$row['option_value'], true) : $default;
    }

    public function setOption(string $pluginId, string $key, mixed $value, bool $autoload = false): void {
        $stmt = $this->db->prepare("INSERT INTO plugin_options(plugin_id,option_key,option_value,autoload,updated_at)
            VALUES(:id,:key,:value,:autoload,:now) ON CONFLICT(plugin_id,option_key) DO UPDATE SET
            option_value=excluded.option_value,autoload=excluded.autoload,updated_at=excluded.updated_at");
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->bindValue(':value', json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), SQLITE3_TEXT);
        $stmt->bindValue(':autoload', $autoload ? 1 : 0, SQLITE3_INTEGER);
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->execute();
    }

    public function deleteOption(string $pluginId, string $key): void {
        $stmt = $this->db->prepare('DELETE FROM plugin_options WHERE plugin_id=:id AND option_key=:key');
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->bindValue(':key', $key, SQLITE3_TEXT);
        $stmt->execute();
    }

    public function deleteOptionsByPrefix(string $pluginId, string $prefix): void {
        $stmt = $this->db->prepare("DELETE FROM plugin_options WHERE plugin_id=:id AND option_key LIKE :prefix ESCAPE '\\'");
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $prefix);
        $stmt->bindValue(':prefix', $escaped . '%', SQLITE3_TEXT);
        $stmt->execute();
    }

    public function log(string $pluginId, string $level, string $message, array $context = []): void {
        $stmt = $this->db->prepare('INSERT INTO plugin_logs(plugin_id,level,message,context_json,created_at) VALUES(:id,:level,:message,:context,:now)');
        $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
        $stmt->bindValue(':level', $level, SQLITE3_TEXT);
        $stmt->bindValue(':message', substr($message, 0, 8000), SQLITE3_TEXT);
        $stmt->bindValue(':context', json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), SQLITE3_TEXT);
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->execute();
    }

    public function logs(string $pluginId = '', int $limit = 100): array {
        $limit = max(1, min($limit, 500));
        if ($pluginId !== '') {
            $stmt = $this->db->prepare("SELECT * FROM plugin_logs WHERE plugin_id=:id ORDER BY id DESC LIMIT {$limit}");
            $stmt->bindValue(':id', $pluginId, SQLITE3_TEXT);
            $result = $stmt->execute();
        } else {
            $result = $this->db->query("SELECT * FROM plugin_logs ORDER BY id DESC LIMIT {$limit}");
        }
        $rows = [];
        while ($result && ($row = $result->fetchArray(SQLITE3_ASSOC))) $rows[] = $row;
        return $rows;
    }
}
