<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class NotificationMessage { public function __construct(public readonly string $recipient, public readonly string $subject, public readonly string $body, public readonly array $data = []) {} }
