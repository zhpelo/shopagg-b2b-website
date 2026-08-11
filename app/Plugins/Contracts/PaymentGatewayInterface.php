<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\PaymentRequest;
use App\Plugins\DTO\PaymentResult;
use App\Plugins\DTO\RefundRequest;
interface PaymentGatewayInterface { public function id(): string; public function pay(PaymentRequest $request): PaymentResult; public function webhook(string $rawBody, array $headers): PaymentResult; public function refund(RefundRequest $request): PaymentResult; }
