<?php

declare(strict_types=1);

// A database of its own (per parallel worker), so the per-test resets of the
// main integration fixture never drop it while TestDatabase holds it open.
$env = getenv();

return [
    'driver' => 'pgsql',
    'host' => $env['DB_HOST'] ?? '127.0.0.1',
    'port' => (int) ($env['DB_PORT'] ?? 5432),
    'database' => databaseTestingDatabaseName($env),
    'username' => $env['DB_USERNAME'] ?? 'marko',
    'password' => $env['DB_PASSWORD'] ?? 'marko',
];
