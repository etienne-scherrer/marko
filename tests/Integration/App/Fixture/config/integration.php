<?php

declare(strict_types=1);

// Read the way the docs tell applications to read environment variables, so
// ConfigTest can prove a variable set only in the real process environment
// reaches config (#160).
return [
    'environment_probe' => $_ENV['MARKO_INTEGRATION_ENV_PROBE'] ?? 'not set',
];
