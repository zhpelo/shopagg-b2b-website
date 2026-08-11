<?php
declare(strict_types=1);

namespace App\Plugins\Jobs;

use App\Core\Database;
use SQLite3;

final class JobQueue {
    private SQLite3 $db;
    public function __construct(private string $pluginId) { $this->db = Database::getInstance(); }

    public function dispatch(string $name, string $handler, array $payload = [], ?string $idempotencyKey = null, ?\DateTimeInterface $runAt = null, int $maxAttempts = 3, int $recurrenceSeconds = 0): int {
        $now = gmdate('c');
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO plugin_jobs(plugin_id,job_name,handler,payload_json,idempotency_key,status,max_attempts,recurrence_seconds,run_at,created_at,updated_at)
            VALUES(:plugin,:name,:handler,:payload,:key,'pending',:max,:recurrence,:run_at,:now,:now)");
        $stmt->bindValue(':plugin', $this->pluginId, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':handler', $handler, SQLITE3_TEXT);
        $stmt->bindValue(':payload', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), SQLITE3_TEXT);
        $stmt->bindValue(':key', $idempotencyKey, $idempotencyKey === null ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindValue(':max', max(1, $maxAttempts), SQLITE3_INTEGER);
        $stmt->bindValue(':recurrence', max(0, $recurrenceSeconds), SQLITE3_INTEGER);
        $stmt->bindValue(':run_at', ($runAt ? $runAt->setTimezone(new \DateTimeZone('UTC'))->format(DATE_ATOM) : $now), SQLITE3_TEXT);
        $stmt->bindValue(':now', $now, SQLITE3_TEXT);
        $stmt->execute();
        if ($this->db->changes() > 0) return $this->db->lastInsertRowID();
        if ($idempotencyKey !== null) {
            $lookup = $this->db->prepare('SELECT id FROM plugin_jobs WHERE plugin_id=:plugin AND idempotency_key=:key');
            $lookup->bindValue(':plugin', $this->pluginId, SQLITE3_TEXT);
            $lookup->bindValue(':key', $idempotencyKey, SQLITE3_TEXT);
            return (int)($lookup->execute()?->fetchArray(SQLITE3_ASSOC)['id'] ?? 0);
        }
        return 0;
    }

    public function schedule(string $name, string $handler, int $everySeconds, array $payload = []): int {
        if ($everySeconds < 60) throw new \InvalidArgumentException('周期任务最短间隔为 60 秒');
        return $this->dispatch($name, $handler, $payload, 'schedule:' . $name, new \DateTimeImmutable('now'), 3, $everySeconds);
    }
}
