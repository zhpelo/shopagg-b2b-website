<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class RefundRequest { public function __construct(public readonly string $transactionId, public readonly Money $amount, public readonly string $reason = '', public readonly array $metadata = []) {} }
