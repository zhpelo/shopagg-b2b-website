<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\Money;
interface CartManagerInterface { public function items(): array; public function add(string|int $productId, int $quantity = 1, array $options = []): void; public function remove(string $lineId): void; public function total(): Money; public function clear(): void; }
