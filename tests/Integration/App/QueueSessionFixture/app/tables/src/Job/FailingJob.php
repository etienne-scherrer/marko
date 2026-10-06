<?php

declare(strict_types=1);

namespace Marko\Integration\QueueSession\Job;

use Marko\Queue\Job;
use RuntimeException;

/**
 * Throws on its only attempt (config/queue.php sets max_attempts to 1), so the worker moves it to failed_jobs.
 */
class FailingJob extends Job
{
    public function handle(): void
    {
        throw new RuntimeException('FailingJob failed on purpose');
    }
}
