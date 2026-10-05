<?php

declare(strict_types=1);

// What an application writes to point marko/cache-redis at its server.
// Today the driver never reads it and always connects to 127.0.0.1:6379
// database 0 (#166), so the suite runs Redis on that address.
$env = getenv();

return [
    'host' => $env['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($env['REDIS_PORT'] ?? 6379),
    'password' => ($env['REDIS_PASSWORD'] ?? '') !== '' ? $env['REDIS_PASSWORD'] : null,
    'database' => 0,
];
