<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class PaymentResult { public function __construct(public readonly string $status, public readonly ?string $transactionId = null, public readonly ?string $redirectUrl = null, public readonly array $data = []) {} }
