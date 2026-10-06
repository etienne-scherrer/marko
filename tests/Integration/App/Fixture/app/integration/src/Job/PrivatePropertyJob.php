<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Job;

use Marko\Queue\Job;

/**
 * serialize() writes a private property's name as "\0ClassName\0property",
 * so this job's serialized form contains NUL bytes, which a Postgres TEXT
 * column cannot store. The queue must still round-trip it (#161).
 */
class PrivatePropertyJob extends Job
{
    public function __construct(
        private string $markerPath,
    ) {}

    public function handle(): void
    {
        file_put_contents($this->markerPath, 'private property job ran');
    }
}
