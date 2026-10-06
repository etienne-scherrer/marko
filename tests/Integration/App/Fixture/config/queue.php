<?php

declare(strict_types=1);

return [
    'driver' => 'database',
    'connection' => 'default',
    'queue' => 'default',
    'retry_after' => 90,
    'max_attempts' => 3,
    // A fixed delay that differs from the built-in curve (2^attempts * 10, so
    // 20 seconds after the first attempt), which proves the config wins (#162).
    'backoff' => 45,
];
