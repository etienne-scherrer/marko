<?php

declare(strict_types=1);

use Marko\Config\ConfigRepositoryInterface;

/*
 * Configuration through the real boot against real services.
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this, migrate: false));

afterEach(function (): void {
    unset($_ENV['MARKO_INTEGRATION_ENV_PROBE']);
    putenv('MARKO_INTEGRATION_ENV_PROBE');
    tearDownIntegrationTest($this);
});

it('honours a config value supplied only as a real environment variable', function (): void {
    // Simulate variables_order without E: the variable exists in the process
    // environment only, never in $_ENV, until the application boots.
    unset($_ENV['MARKO_INTEGRATION_ENV_PROBE']);
    putenv('MARKO_INTEGRATION_ENV_PROBE=from the process environment');

    $app = bootIntegrationApp($this->project, migrate: false);

    expect($app->container->get(ConfigRepositoryInterface::class)->getString('integration.environment_probe'))
        ->toBe('from the process environment');
})->issue(160);
