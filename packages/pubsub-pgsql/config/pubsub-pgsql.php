<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'host' => Env::string('PUBSUB_PGSQL_HOST', '127.0.0.1'),
    'port' => Env::int('PUBSUB_PGSQL_PORT', 5432, min: 1, max: 65535),
    'user' => Env::nullableString('PUBSUB_PGSQL_USER'),
    'password' => Env::nullableString('PUBSUB_PGSQL_PASSWORD'),
    'database' => Env::nullableString('PUBSUB_PGSQL_DATABASE'),
];
