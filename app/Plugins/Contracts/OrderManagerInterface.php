<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\OrderRef;
interface OrderManagerInterface { public function find(string|int $id): ?OrderRef; public function changeStatus(string|int $id, string $status): OrderRef; public function forMember(string|int $memberId, array $filters = []): array; }
