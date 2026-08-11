<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\ImportBatchResult;
interface DataCollectorInterface { public function id(): string; public function collect(array $configuration, mixed $cursor = null): ImportBatchResult; }
