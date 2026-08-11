<?php
declare(strict_types=1);

namespace App\Plugins\Jobs;

use App\Core\Database;
use App\Plugins\Contracts\ChunkedJobInterface;
use App\Plugins\PluginRuntime;
use App\Plugins\Support\Handler;
use SQLite3;

final class JobRunner {
    private SQLite3 $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function runDue(int $limit = 1): array {
        $results = [];
        for ($i = 0; $i < max(1, min($limit, 20)); $i++) {
            $job = $this->claim();
            if ($job === null) break;
            $results[] = $this->execute($job);
        }
        return $results;
    }

    public function retry(int $jobId): void {
        $stmt = $this->db->prepare("UPDATE plugin_jobs SET status='pending',attempts=0,last_error=NULL,locked_at=NULL,locked_by=NULL,run_at=:now,updated_at=:now WHERE id=:id AND status IN ('failed','cancelled','retry')");
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $jobId, SQLITE3_INTEGER);
        $stmt->execute();
        if ($this->db->changes() < 1) throw new \RuntimeException('任务不存在或当前状态不能重试');
    }

    public function cancel(int $jobId): void {
        $stmt = $this->db->prepare("UPDATE plugin_jobs SET status='cancelled',locked_at=NULL,locked_by=NULL,updated_at=:now WHERE id=:id AND status IN ('pending','retry','failed')");
        $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
        $stmt->bindValue(':id', $jobId, SQLITE3_INTEGER);
        $stmt->execute();
        if ($this->db->changes() < 1) throw new \RuntimeException('任务不存在或当前状态不能取消');
    }

    private function claim(): ?array {
        $worker = bin2hex(random_bytes(8));
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $now = gmdate('c');
            $stale = $this->db->prepare("UPDATE plugin_jobs SET status='retry',locked_at=NULL,locked_by=NULL,last_error='任务锁超时，已自动重试',updated_at=:now WHERE status='running' AND locked_at<:stale");
            $stale->bindValue(':now', $now, SQLITE3_TEXT);
            $stale->bindValue(':stale', gmdate('c', time() - 900), SQLITE3_TEXT);
            $stale->execute();
            $stmt = $this->db->prepare("SELECT j.* FROM plugin_jobs j INNER JOIN plugins p ON p.plugin_id=j.plugin_id AND p.status='enabled' WHERE j.status IN ('pending','retry') AND j.run_at<=:now ORDER BY j.run_at,j.id LIMIT 1");
            $stmt->bindValue(':now', $now, SQLITE3_TEXT);
            $row = $stmt->execute()?->fetchArray(SQLITE3_ASSOC) ?: null;
            if ($row === null) { $this->db->exec('COMMIT'); return null; }
            $update = $this->db->prepare("UPDATE plugin_jobs SET status='running',locked_at=:now,locked_by=:worker,attempts=attempts+1,updated_at=:now WHERE id=:id AND status IN ('pending','retry')");
            $update->bindValue(':now', $now, SQLITE3_TEXT);
            $update->bindValue(':worker', $worker, SQLITE3_TEXT);
            $update->bindValue(':id', (int)$row['id'], SQLITE3_INTEGER);
            $update->execute();
            $this->db->exec('COMMIT');
            return $this->db->changes() > 0 ? $row : null;
        } catch (\Throwable $e) { $this->db->exec('ROLLBACK'); throw $e; }
    }

    private function execute(array $job): array {
        $started = microtime(true);
        $status = 'completed';
        $message = '';
        try {
            $context = PluginRuntime::instance()->context($job['plugin_id']);
            $jobContext = new JobContext((int)$job['id'], json_decode((string)$job['payload_json'], true) ?: [], $context);
            [$class, $method] = Handler::parse($job['handler']);
            $instance = new $class();
            $cursor = $job['cursor_json'] === null ? null : json_decode((string)$job['cursor_json'], true);
            PluginRuntime::beginInvocation($job['plugin_id']);
            try { $result = $instance instanceof ChunkedJobInterface ? $instance->handle($jobContext, $cursor) : $instance->$method($jobContext, $cursor); }
            finally { PluginRuntime::endInvocation(); }
            if (!$result instanceof JobResult) $result = JobResult::complete();
            $recurrence = (int)($job['recurrence_seconds'] ?? 0);
            $status = $result->complete && $recurrence === 0 ? 'completed' : 'pending';
            $stmt = $this->db->prepare('UPDATE plugin_jobs SET status=:status,progress=:progress,cursor_json=:cursor,locked_at=NULL,locked_by=NULL,last_error=NULL,run_at=:run_at,updated_at=:now WHERE id=:id');
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':progress', $result->complete && $recurrence > 0 ? 0 : $result->progress, SQLITE3_INTEGER);
            $nextCursor = $result->complete && $recurrence > 0 ? null : $result->cursor;
            $stmt->bindValue(':cursor', $nextCursor === null ? null : json_encode($nextCursor), $nextCursor === null ? SQLITE3_NULL : SQLITE3_TEXT);
            $stmt->bindValue(':run_at', gmdate('c', time() + ($result->complete ? $recurrence : 1)), SQLITE3_TEXT);
            $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
            $stmt->bindValue(':id', (int)$job['id'], SQLITE3_INTEGER);
            $stmt->execute();
            $message = $result->message;
        } catch (\Throwable $e) {
            $attempts = (int)$job['attempts'] + 1;
            $status = $attempts >= (int)$job['max_attempts'] ? 'failed' : 'retry';
            $message = $e->getMessage();
            $delay = min(3600, 30 * (2 ** max(0, $attempts - 1)));
            $stmt = $this->db->prepare('UPDATE plugin_jobs SET status=:status,last_error=:error,locked_at=NULL,locked_by=NULL,run_at=:run_at,updated_at=:now WHERE id=:id');
            $stmt->bindValue(':status', $status, SQLITE3_TEXT);
            $stmt->bindValue(':error', substr($message, 0, 4000), SQLITE3_TEXT);
            $stmt->bindValue(':run_at', gmdate('c', time() + $delay), SQLITE3_TEXT);
            $stmt->bindValue(':now', gmdate('c'), SQLITE3_TEXT);
            $stmt->bindValue(':id', (int)$job['id'], SQLITE3_INTEGER);
            $stmt->execute();
            PluginRuntime::instance()->registry()->recordFailure($job['plugin_id'], $e);
        }
        $duration = (int)((microtime(true) - $started) * 1000);
        $run = $this->db->prepare('INSERT INTO plugin_job_runs(job_id,plugin_id,started_at,finished_at,status,duration_ms,message) VALUES(:job,:plugin,:started,:finished,:status,:duration,:message)');
        $run->bindValue(':job', (int)$job['id'], SQLITE3_INTEGER);
        $run->bindValue(':plugin', $job['plugin_id'], SQLITE3_TEXT);
        $run->bindValue(':started', gmdate('c', (int)$started), SQLITE3_TEXT);
        $run->bindValue(':finished', gmdate('c'), SQLITE3_TEXT);
        $run->bindValue(':status', $status, SQLITE3_TEXT);
        $run->bindValue(':duration', $duration, SQLITE3_INTEGER);
        $run->bindValue(':message', $message, SQLITE3_TEXT);
        $run->execute();
        return ['job_id' => (int)$job['id'], 'status' => $status, 'message' => $message];
    }
}
