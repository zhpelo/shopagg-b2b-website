<?php
declare(strict_types=1);

namespace App\Plugins\Jobs;

use App\Plugins\PluginContext;

final class JobContext {
    private float $startedAt;
    public function __construct(public readonly int $jobId, public readonly array $payload, public readonly PluginContext $plugin, private int $budgetSeconds = 8) { $this->startedAt = microtime(true); }
    public function shouldYield(): bool { return (microtime(true) - $this->startedAt) >= $this->budgetSeconds; }
}
