<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class PointsChange { public function __construct(public readonly string|int $memberId, public readonly int $amount, public readonly string $reason, public readonly ?string $reference = null) {} }
