<?php

declare(strict_types=1);

// Connection details come from the real process environment so CI services
// and local `docker compose` runs need no file edits. The database name is
// per parallel worker; see integrationDatabaseName().
$env = getenv();

return [
    'driver' => 'pgsql',
    'host' => $env['DB_HOST'] ?? '127.0.0.1',
    'port' => (int) ($env['DB_PORT'] ?? 5432),
    'database' => integrationDatabaseName($env),
    'username' => $env['DB_USERNAME'] ?? 'marko',
    'password' => $env['DB_PASSWORD'] ?? 'marko',
    'migrations' => [
        // Created by hand in 2026_01_01_000006; db:migrate must leave it alone.
        'ignore_indexes' => ['authors_named_idx'],
    ],
];
