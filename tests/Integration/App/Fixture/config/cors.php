<?php

declare(strict_types=1);

return [
    'paths' => ['*'],
    'allowed_origins' => ['https://app.example.test'],
    'allowed_methods' => ['GET', 'POST'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'expose_headers' => [],
    'supports_credentials' => false,
    'max_age' => 600,
];
