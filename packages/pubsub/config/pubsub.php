<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'driver' => Env::string('PUBSUB_DRIVER', 'redis'),
    'prefix' => Env::string('PUBSUB_PREFIX', 'marko:'),
];
