<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\MemberRef;
interface MemberIdentityProviderInterface { public function current(): ?MemberRef; public function find(string|int $id): ?MemberRef; public function login(string $identifier, string $password): ?MemberRef; public function logout(): void; }
