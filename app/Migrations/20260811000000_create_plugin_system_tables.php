<?php
declare(strict_types=1);

return new class {
    public function up(SQLite3 $db): void {
        $db->exec("CREATE TABLE IF NOT EXISTS plugins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plugin_id TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            vendor TEXT NOT NULL DEFAULT '',
            description TEXT NOT NULL DEFAULT '',
            installed_version TEXT NOT NULL,
            active_version TEXT,
            status TEXT NOT NULL DEFAULT 'installed',
            source TEXT NOT NULL DEFAULT 'private',
            manifest_json TEXT NOT NULL,
            active_manifest_json TEXT,
            failure_count INTEGER NOT NULL DEFAULT 0,
            last_error TEXT,
            installed_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_plugins_status ON plugins(status)");

        $db->exec("CREATE TABLE IF NOT EXISTS plugin_options (
            plugin_id TEXT NOT NULL,
            option_key TEXT NOT NULL,
            option_value TEXT,
            autoload INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL,
            PRIMARY KEY (plugin_id, option_key)
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS plugin_migrations (
            plugin_id TEXT NOT NULL,
            version TEXT NOT NULL,
            name TEXT NOT NULL DEFAULT '',
            executed_at TEXT NOT NULL,
            execution_time INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (plugin_id, version)
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS plugin_jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plugin_id TEXT NOT NULL,
            job_name TEXT NOT NULL,
            handler TEXT NOT NULL,
            payload_json TEXT NOT NULL DEFAULT '{}',
            cursor_json TEXT,
            idempotency_key TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            progress INTEGER NOT NULL DEFAULT 0,
            attempts INTEGER NOT NULL DEFAULT 0,
            max_attempts INTEGER NOT NULL DEFAULT 3,
            recurrence_seconds INTEGER NOT NULL DEFAULT 0,
            run_at TEXT NOT NULL,
            locked_at TEXT,
            locked_by TEXT,
            last_error TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_plugin_jobs_due ON plugin_jobs(status, run_at)");
        $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_plugin_jobs_idempotency ON plugin_jobs(plugin_id, idempotency_key) WHERE idempotency_key IS NOT NULL");

        $db->exec("CREATE TABLE IF NOT EXISTS plugin_job_runs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            job_id INTEGER NOT NULL,
            plugin_id TEXT NOT NULL,
            started_at TEXT NOT NULL,
            finished_at TEXT,
            status TEXT NOT NULL,
            duration_ms INTEGER NOT NULL DEFAULT 0,
            message TEXT,
            FOREIGN KEY (job_id) REFERENCES plugin_jobs(id) ON DELETE CASCADE
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS plugin_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            plugin_id TEXT NOT NULL,
            level TEXT NOT NULL,
            message TEXT NOT NULL,
            context_json TEXT NOT NULL DEFAULT '{}',
            created_at TEXT NOT NULL
        )");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_plugin_logs_plugin_created ON plugin_logs(plugin_id, created_at DESC)");
    }

    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS plugin_job_runs');
        $db->exec('DROP TABLE IF EXISTS plugin_jobs');
        $db->exec('DROP TABLE IF EXISTS plugin_logs');
        $db->exec('DROP TABLE IF EXISTS plugin_migrations');
        $db->exec('DROP TABLE IF EXISTS plugin_options');
        $db->exec('DROP TABLE IF EXISTS plugins');
    }
};
