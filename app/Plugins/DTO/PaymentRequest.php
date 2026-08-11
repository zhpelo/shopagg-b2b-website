<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class PaymentRequest { public function __construct(public readonly OrderRef $order, public readonly Money $amount, public readonly string $returnUrl, public readonly string $notifyUrl, public readonly array $metadata = []) {} }
