<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    'key' => Env::string('ENCRYPTION_KEY', ''),
    'cipher' => Env::string('ENCRYPTION_CIPHER', 'aes-256-gcm'),
];
