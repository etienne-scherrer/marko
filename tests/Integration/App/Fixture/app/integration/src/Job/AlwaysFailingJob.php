<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Job;

use Marko\Queue\Job;
use RuntimeException;

/**
 * Throws on every attempt, so it must end up in failed_jobs once it has used
 * its max_attempts.
 */
class AlwaysFailingJob extends Job
{
    public function handle(): void
    {
        throw new RuntimeException('AlwaysFailingJob failed on purpose');
    }
}
