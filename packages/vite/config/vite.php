<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'entry' => Env::string('VITE_ENTRY', ''),
    'buildDirectory' => 'build',
    'manifestFilename' => '.vite/manifest.json',
    'devServerUrl' => Env::string('VITE_DEV_SERVER_URL', ''),
    'devServerStylesheets' => [],
    'useDevServer' => Env::bool('VITE_USE_DEV_SERVER', Env::string('APP_ENV', 'local') === 'local'),
];
