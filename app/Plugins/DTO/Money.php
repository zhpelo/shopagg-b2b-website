<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class Money { public function __construct(public readonly int $minorAmount, public readonly string $currency) {} public function decimal(): float { return $this->minorAmount / 100; } }
