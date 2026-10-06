<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'version' => null,
    'assetEntry' => null,
    'ssr' => [
        'enabled' => Env::bool('INERTIA_SSR_ENABLED', false),
        'url' => Env::string('INERTIA_SSR_URL', 'http://localhost:13714'),
    ],
];
