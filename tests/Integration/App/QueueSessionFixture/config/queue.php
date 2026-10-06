<?php

declare(strict_types=1);

return [
    'driver' => 'database',
    'connection' => 'default',
    'queue' => 'default',
    'retry_after' => 90,
    // One attempt, so a failing job lands in failed_jobs on the first queue:work.
    'max_attempts' => 1,
    'backoff' => 0,
];
