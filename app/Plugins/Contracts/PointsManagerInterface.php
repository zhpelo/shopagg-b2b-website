<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\PointsChange;
interface PointsManagerInterface { public function balance(string|int $memberId): int; public function apply(PointsChange $change): int; }
