<?php

declare(strict_types=1);

// A fixed, insecure key: fine for a test fixture, never for a real application.
return [
    'key' => base64_encode(str_repeat('k', 32)),
    'cipher' => 'aes-256-gcm',
];
