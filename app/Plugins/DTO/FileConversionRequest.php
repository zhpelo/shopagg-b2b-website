<?php
declare(strict_types=1);
namespace App\Plugins\DTO;
final class FileConversionRequest { public function __construct(public readonly string $sourcePath, public readonly string $targetFormat, public readonly array $options = []) {} }
