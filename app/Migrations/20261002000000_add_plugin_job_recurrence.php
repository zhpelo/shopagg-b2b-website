<?php
declare(strict_types=1);

/** Backfill the recurring-job column for databases created by an older plugin schema. */
return new class {
    public function up(SQLite3 $db): void {
        $columns = [];
        $result = $db->query('PRAGMA table_info(plugin_jobs)');
        while ($result && ($row = $result->fetchArray(SQLITE3_ASSOC))) $columns[(string)$row['name']] = true;
        if ($columns !== [] && !isset($columns['recurrence_seconds'])) {
            if (!$db->exec('ALTER TABLE plugin_jobs ADD COLUMN recurrence_seconds INTEGER NOT NULL DEFAULT 0')) {
                throw new RuntimeException('Unable to add plugin_jobs.recurrence_seconds: ' . $db->lastErrorMsg());
            }
        }
    }

    public function down(SQLite3 $db): void {
        // Kept for forward compatibility with the plugin job runner.
    }
};
