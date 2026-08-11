<?php
declare(strict_types=1);

namespace App\Plugins\Contracts;

use App\Plugins\Jobs\JobContext;
use App\Plugins\Jobs\JobResult;

interface ChunkedJobInterface {
    public function handle(JobContext $context, mixed $cursor): JobResult;
}
