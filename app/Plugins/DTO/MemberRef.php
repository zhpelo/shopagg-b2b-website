<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class MemberRef { public function __construct(public readonly string|int $id, public readonly string $displayName, public readonly ?string $email = null, public readonly array $metadata = []) {} }
