<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;

/*
 * Strict session ids through the real module wiring and the database
 * session handler against Postgres (#266): only a session the store knows
 * is resumed, and replaying an unknown cookie stores nothing.
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

it('creates no session row when an unknown cookie is replayed repeatedly', function (): void {
    $unknownId = str_repeat('a', 40);
    $cookieValues = [];

    for ($i = 0; $i < 5; $i++) {
        $response = $this->app->router->handle(
            integrationRequest('GET', '/health', cookies: ['marko_session' => $unknownId]),
        );
        $cookieValues[] = integrationCookieValue($response, 'marko_session');
    }

    $rows = $this->app->container->get(ConnectionInterface::class)->query('SELECT id FROM sessions');

    expect($rows)->toBeEmpty()
        ->and($cookieValues)->toBe(['', '', '', '', '']);
})->issue(266);

it('treats a session row older than the lifetime as unknown', function (): void {
    $first = $this->app->router->handle(integrationRequest('GET', '/visits'));
    $sessionId = (string) integrationCookieValue($first, 'marko_session');
    $connection = $this->app->container->get(ConnectionInterface::class);
    $connection->execute(
        'UPDATE sessions SET last_activity = ? WHERE id = ?',
        [time() - 121 * 60, $sessionId],
    );

    $second = $this->app->router->handle(
        integrationRequest('GET', '/visits', cookies: ['marko_session' => $sessionId]),
    );

    expect($second->body())->toBe('1')
        ->and(integrationCookieValue($second, 'marko_session'))->not->toBe($sessionId)
        ->and(integrationCookieValue($second, 'marko_session'))->not->toBe('');
})->issue(266);

it('refreshes last activity of a resumed session that the request did not modify', function (): void {
    $first = $this->app->router->handle(integrationRequest('GET', '/visits'));
    $sessionId = (string) integrationCookieValue($first, 'marko_session');
    $connection = $this->app->container->get(ConnectionInterface::class);
    $staleActivity = time() - 30 * 60;
    $connection->execute(
        'UPDATE sessions SET last_activity = ? WHERE id = ?',
        [$staleActivity, $sessionId],
    );
    $payloadBefore = $connection->query('SELECT payload FROM sessions WHERE id = ?', [$sessionId]);

    $second = $this->app->router->handle(
        integrationRequest('GET', '/health', cookies: ['marko_session' => $sessionId]),
    );
    $row = $connection->query('SELECT payload, last_activity FROM sessions WHERE id = ?', [$sessionId]);

    expect($second->cookies())->toBeEmpty()
        ->and($row[0]['payload'])->toBe($payloadBefore[0]['payload'])
        ->and((int) $row[0]['last_activity'])->toBeGreaterThan($staleActivity);
})->issue(266);
