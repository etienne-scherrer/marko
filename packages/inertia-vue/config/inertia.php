<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'assetEntry' => Env::string('INERTIA_VUE_CLIENT_ENTRY', 'app/vue-web/resources/js/app.js'),
];
