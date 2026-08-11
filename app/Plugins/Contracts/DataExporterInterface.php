<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
interface DataExporterInterface { public function id(): string; public function formats(): array; public function export(string $format, array $filters = []): iterable; }
