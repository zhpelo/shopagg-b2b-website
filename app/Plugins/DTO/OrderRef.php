<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class OrderRef { public function __construct(public readonly string|int $id, public readonly string $number, public readonly string|int|null $memberId, public readonly Money $total, public readonly string $status, public readonly array $metadata = []) {} }
