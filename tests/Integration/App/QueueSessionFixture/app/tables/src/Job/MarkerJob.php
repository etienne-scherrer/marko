<?php

declare(strict_types=1);

namespace Marko\Integration\QueueSession\Job;

use Marko\Queue\Job;

/**
 * Succeeds, leaving a marker file behind so a test can prove the worker ran it.
 */
class MarkerJob extends Job
{
    public function __construct(
        public string $markerPath,
    ) {}

    public function handle(): void
    {
        file_put_contents($this->markerPath, 'ran');
    }
}
