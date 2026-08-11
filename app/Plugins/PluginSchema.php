<?php
declare(strict_types=1);

namespace App\Plugins;

final class PluginSchema {
    public function __construct(private PluginDatabase $database) {}

    /** @param array<string,string> $columns */
    public function create(string $logicalName, array $columns, array $indexes = []): void {
        if ($columns === []) throw new \InvalidArgumentException('数据表至少包含一个字段');
        $definitions = [];
        foreach ($columns as $name => $definition) {
            if (preg_match('/^[a-z][a-z0-9_]*$/D', $name) !== 1) throw new \InvalidArgumentException("字段名无效：{$name}");
            if (!is_string($definition) || preg_match('/[;\x00]/', $definition)) throw new \InvalidArgumentException("字段定义无效：{$name}");
            $definitions[] = '"' . $name . '" ' . $definition;
        }
        $table = $this->database->table($logicalName);
        $this->database->exec('CREATE TABLE IF NOT EXISTS "' . $table . '" (' . implode(',', $definitions) . ')');
        foreach ($indexes as $name => $fields) {
            $this->index($logicalName, (string)$name, (array)$fields);
        }
    }

    public function index(string $logicalName, string $indexName, array $fields, bool $unique = false): void {
        if (preg_match('/^[a-z][a-z0-9_]*$/D', $indexName) !== 1 || $fields === []) throw new \InvalidArgumentException('索引定义无效');
        foreach ($fields as $field) if (preg_match('/^[a-z][a-z0-9_]*$/D', (string)$field) !== 1) throw new \InvalidArgumentException('索引字段无效');
        $table = $this->database->table($logicalName);
        $index = $table . '_' . $indexName;
        $columns = implode(',', array_map(static fn($field): string => '"' . $field . '"', $fields));
        $this->database->exec('CREATE ' . ($unique ? 'UNIQUE ' : '') . 'INDEX IF NOT EXISTS "' . $index . '" ON "' . $table . '" (' . $columns . ')');
    }

    public function exists(string $logicalName): bool {
        $table = $this->database->table($logicalName);
        return $this->database->fetchOne('SELECT name FROM sqlite_master WHERE type="table" AND name=:name', [':name' => $table]) !== null;
    }

    public function drop(string $logicalName): void {
        $this->database->exec('DROP TABLE IF EXISTS "' . $this->database->table($logicalName) . '"');
    }

    public function pluginTables(): array {
        $sample = $this->database->table('table_marker');
        $prefix = substr($sample, 0, -strlen('table_marker'));
        return array_column($this->database->fetchAll('SELECT name FROM sqlite_master WHERE type="table" AND name LIKE :prefix', [':prefix' => $prefix . '%']), 'name');
    }
}
