<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
interface FormTypeProviderInterface { public function id(): string; public function fields(): array; public function validate(array $input): array; public function submit(array $input): mixed; }
