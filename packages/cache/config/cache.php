<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'driver' => Env::string('CACHE_DRIVER', 'file'),
    'path' => Env::string('CACHE_PATH', 'storage/cache'),
    'default_ttl' => Env::int('CACHE_TTL', 3600, min: 0),
];
