<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'locale' => Env::string('APP_LOCALE', 'en'),
    'fallback_locale' => Env::string('APP_FALLBACK_LOCALE', 'en'),
];
