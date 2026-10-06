<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'shutdown_timeout' => Env::int('AMPHP_SHUTDOWN_TIMEOUT', 30, min: 0),
    'channels' => Env::list('AMPHP_CHANNELS', []),
];
