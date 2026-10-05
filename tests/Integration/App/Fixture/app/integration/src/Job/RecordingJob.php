<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Job;

use Marko\Queue\Job;

/**
 * Succeeds, leaving a marker file behind so a test can prove it ran in the
 * worker rather than inline.
 */
class RecordingJob extends Job
{
    public function __construct(
        public string $markerPath,
        public string $message,
    ) {}

    public function handle(): void
    {
        file_put_contents($this->markerPath, $this->message);
    }
}
