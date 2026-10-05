<?php

declare(strict_types=1);

// What an application writes to point marko/cache-redis at its server. It
// reads getenv() rather than $_ENV because the fixture does not install
// marko/env, and PHP only fills $_ENV when variables_order contains E. The
// non-default database proves the driver honours every connection setting.
$env = getenv();

return [
    'host' => $env['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($env['REDIS_PORT'] ?? 6379),
    'password' => ($env['REDIS_PASSWORD'] ?? '') !== '' ? $env['REDIS_PASSWORD'] : null,
    'database' => 2,
];
