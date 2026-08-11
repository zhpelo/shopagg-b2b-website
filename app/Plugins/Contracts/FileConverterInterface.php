<?php
declare(strict_types=1);
namespace App\Plugins\Contracts;
use App\Plugins\DTO\FileConversionRequest;
interface FileConverterInterface { public function id(): string; public function supports(string $sourceFormat, string $targetFormat): bool; public function convert(FileConversionRequest $request): string; }
