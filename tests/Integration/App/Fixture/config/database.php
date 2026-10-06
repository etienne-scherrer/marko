<?php

declare(strict_types=1);

// Connection details come from the real process environment, through the
// documented $_ENV lookup (Application::boot() mirrors real variables into
// $_ENV, #160), so CI services and local `docker compose` runs need no file
// edits. The database name is per parallel worker; see
// integrationDatabaseName().
return [
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
    'database' => integrationDatabaseName($_ENV),
    'username' => $_ENV['DB_USERNAME'] ?? 'marko',
    'password' => $_ENV['DB_PASSWORD'] ?? 'marko',
    'migrations' => [
        // Created by hand in 2026_01_01_000006; db:migrate must leave it alone.
        'ignore_indexes' => ['authors_named_idx'],
    ],
];
