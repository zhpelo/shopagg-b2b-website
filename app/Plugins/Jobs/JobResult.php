<?php
declare(strict_types=1);

namespace App\Plugins\Jobs;

final class JobResult {
    private function __construct(public readonly bool $complete, public readonly mixed $cursor, public readonly int $progress, public readonly string $message) {}
    public static function complete(string $message = ''): self { return new self(true, null, 100, $message); }
    public static function continue(mixed $cursor, int $progress = 0, string $message = ''): self { return new self(false, $cursor, max(0, min(99, $progress)), $message); }
}
