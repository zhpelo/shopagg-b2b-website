<?php
declare(strict_types=1);

namespace App\Plugins\Contracts;

use App\Plugins\DTO\PaymentResult;

/** Optional companion service for gateways that can query/capture a payment. */
interface PaymentReconcilerInterface
{
    public function reconcile(string $transactionId, array $context = []): PaymentResult;
}
