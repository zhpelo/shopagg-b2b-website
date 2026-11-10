<?php
declare(strict_types=1);

namespace App\Services\Updates;

interface ReleaseProvider
{
    public function latest(): ?array;
    public function releases(int $page = 1, int $perPage = 20): array;
    public function release(string $version): ?array;
    public function source(): string;
}
