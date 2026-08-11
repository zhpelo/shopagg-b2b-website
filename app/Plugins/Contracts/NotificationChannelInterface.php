<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\NotificationMessage;
interface NotificationChannelInterface { public function id(): string; public function send(NotificationMessage $message): string; }
