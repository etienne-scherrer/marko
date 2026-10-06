<?php

declare(strict_types=1);

// What an application writes to point marko/cache-redis at its server: the
// documented $_ENV lookup. Application::boot() mirrors the real process
// environment into $_ENV (#160), so this works even when variables_order
// leaves $_ENV empty. The non-default database proves the driver honours
// every connection setting.
return [
    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    'password' => ($_ENV['REDIS_PASSWORD'] ?? '') !== '' ? $_ENV['REDIS_PASSWORD'] : null,
    'database' => 2,
];
