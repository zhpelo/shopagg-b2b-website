<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\ImportBatchResult;
interface DataImporterInterface { public function id(): string; public function supports(string $format): bool; public function import(string $path, mixed $cursor = null, int $batchSize = 100): ImportBatchResult; }
