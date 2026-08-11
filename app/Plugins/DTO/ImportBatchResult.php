<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class ImportBatchResult { public function __construct(public readonly int $processed, public readonly int $succeeded, public readonly int $failed, public readonly mixed $nextCursor = null, public readonly array $errors = []) {} }
