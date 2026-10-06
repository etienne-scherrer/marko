<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'assetEntry' => Env::string('INERTIA_SVELTE_CLIENT_ENTRY', 'app/svelte-web/resources/js/app.js'),
];
