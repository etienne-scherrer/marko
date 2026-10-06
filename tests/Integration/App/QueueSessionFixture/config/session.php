<?php

declare(strict_types=1);

return [
    'driver' => 'database',
    'lifetime' => 120,
    'expire_on_close' => false,
    'path' => 'storage/sessions',

    'cookie' => [
        'name' => 'marko_session',
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'lax',
    ],

    'gc_probability' => 0,
    'gc_divisor' => 100,
];
