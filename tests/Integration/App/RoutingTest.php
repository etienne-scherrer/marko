<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Routing\RouteCollection;
use Marko\Routing\UrlGeneratorInterface;

/*
 * Named routes, prefixes, constrained parameters and #[WithoutMiddleware]
 * through the real module wiring (#172).
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

it('serves a stateless route without creating a session row', function (): void {
    $response = $this->app->router->handle(integrationRequest('GET', '/api/status/204'));
    $rows = $this->app->container->get(ConnectionInterface::class)->query('SELECT id FROM sessions');

    expect($response->statusCode())->toBe(200)
        ->and(integrationCookieValue($response, 'marko_session'))->toBeNull()
        ->and($rows)->toBeEmpty();
})->issue(172);

it('registers prefixed, named routes with their constraints', function (): void {
    $route = $this->app->container->get(RouteCollection::class)->named('api.status');

    expect($route)->not->toBeNull()
        ->and($route->path)->toBe('/api/status/{code:\d+}')
        ->and($this->app->router->handle(integrationRequest('GET', '/api/status/abc'))->statusCode())->toBe(404);
})->issue(172);

it('generates relative and absolute URLs from the container-bound generator', function (): void {
    $generator = $this->app->container->get(UrlGeneratorInterface::class);
    $response = $this->app->router->handle(integrationRequest('GET', '/api/status/200'));

    expect($generator->route('api.status', ['code' => 200, 'verbose' => 1]))->toBe('/api/status/200?verbose=1')
        ->and($generator->route('api.status', ['code' => 200], absolute: true))->toBe(
            'https://integration.test/api/status/200',
        )
        ->and(json_decode($response->body(), true)['self'])->toBe('/api/status/200');
})->issue(172);
