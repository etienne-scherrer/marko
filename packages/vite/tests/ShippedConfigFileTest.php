<?php

declare(strict_types=1);

use Marko\Config\Exceptions\ConfigException;

const VITE_CONFIG_FILE = __DIR__ . '/../config/vite.php';
const VITE_CONFIG_VARIABLES = ['VITE_USE_DEV_SERVER', 'APP_ENV'];

beforeEach(function (): void {
    $this->originalEnv = [];

    foreach (VITE_CONFIG_VARIABLES as $variable) {
        $this->originalEnv[$variable] = $_ENV[$variable] ?? null;
        unset($_ENV[$variable]);
    }
});

afterEach(function (): void {
    foreach ($this->originalEnv as $variable => $value) {
        if ($value === null) {
            unset($_ENV[$variable]);
        } else {
            $_ENV[$variable] = $value;
        }
    }
});

it('derives vite.useDevServer from APP_ENV when VITE_USE_DEV_SERVER is unset', function (): void {
    expect((require VITE_CONFIG_FILE)['useDevServer'])->toBeTrue();

    $_ENV['APP_ENV'] = 'production';

    expect((require VITE_CONFIG_FILE)['useDevServer'])->toBeFalse();
});

it('lets VITE_USE_DEV_SERVER override APP_ENV', function (): void {
    $_ENV['APP_ENV'] = 'production';
    $_ENV['VITE_USE_DEV_SERVER'] = 'on';

    expect((require VITE_CONFIG_FILE)['useDevServer'])->toBeTrue();
});

it('rejects an unrecognised VITE_USE_DEV_SERVER value', function (): void {
    $_ENV['VITE_USE_DEV_SERVER'] = 'sometimes';

    expect(fn (): array => require VITE_CONFIG_FILE)
        ->toThrow(ConfigException::class, 'Environment variable "VITE_USE_DEV_SERVER" must be a boolean');
});
