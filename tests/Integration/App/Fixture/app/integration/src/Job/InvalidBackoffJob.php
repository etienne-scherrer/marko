<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Job;

use Marko\Queue\Job;
use RuntimeException;

/**
 * Throws with a negative backoff, so the worker cannot release it for a retry
 * and must record it in failed_jobs instead of stopping (#234).
 */
class InvalidBackoffJob extends Job
{
    public function __construct()
    {
        $this->backoff = [-5];
    }

    public function handle(): void
    {
        throw new RuntimeException('InvalidBackoffJob failed on purpose');
    }
}
