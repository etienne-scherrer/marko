<?php

declare(strict_types=1);

// A config file written the way #289 retired: every read below must be reported.
// The words env(, getenv( and (int) in this comment must not be.

return [
    'port' => (int) ($_ENV['FIXTURE_PORT'] ?? 8080),
    'ratio' => (float) getenv('FIXTURE_RATIO'),
    'flag' => filter_var('true', FILTER_VALIDATE_BOOL),
    'debug' => env('FIXTURE_DEBUG', false),
    'strict' => (bool) 'yes',
    'name' => 'not env(\'FIXTURE_NAME\') in a string',
];
