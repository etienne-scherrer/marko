<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Repository\AuthorRepository;

/*
 * Exceptions that carry an HTTP meaning become the matching response
 * through the real routing pipeline, not a 500 (#169).
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

const ERROR_MAPPING_JSON = ['HTTP_ACCEPT' => 'application/json'];

it('answers a validation failure with a 422 JSON response', function (): void {
    // The global CsrfMiddleware guards POST /authors: start a session, then
    // echo the XSRF-TOKEN cookie it issues as an SPA client would.
    $session = $this->app->router->handle(integrationRequest('GET', '/visits'));
    $token = (string) integrationCookieValue($session, 'XSRF-TOKEN');
    $cookies = ['marko_session' => (string) integrationCookieValue($session, 'marko_session'), 'XSRF-TOKEN' => $token];
    $server = [...ERROR_MAPPING_JSON, 'HTTP_X_XSRF_TOKEN' => $token];

    $invalid = $this->app->router->handle(
        integrationRequest('POST', '/authors', cookies: $cookies, server: $server, post: ['name' => 'Al']),
    );
    $valid = $this->app->router->handle(
        integrationRequest('POST', '/authors', cookies: $cookies, server: $server, post: ['name' => 'Alice']),
    );
    $body = json_decode($invalid->body(), true);

    expect($invalid->statusCode())->toBe(422)
        ->and($invalid->headers()['Content-Type'])->toContain('application/json')
        ->and($body)->toHaveKeys(['message', 'errors'])
        ->and($body['errors'])->toHaveKey('name')
        ->and($valid->statusCode())->toBe(201);
})->issue(169);

it('answers a missing entity with 404', function (): void {
    $author = new Author();
    $author->name = 'Found';
    $this->app->container->get(AuthorRepository::class)->save($author);

    $found = $this->app->router->handle(integrationRequest('GET', "/authors/$author->id", server: ERROR_MAPPING_JSON));
    $missing = $this->app->router->handle(integrationRequest('GET', '/authors/999999', server: ERROR_MAPPING_JSON));

    expect($found->statusCode())->toBe(200)
        ->and($missing->statusCode())->toBe(404)
        ->and(json_decode($missing->body(), true))->toBe(['message' => 'Not found.']);
})->issue(169);

it('answers a CSRF failure with 419', function (): void {
    $missing = $this->app->router->handle(integrationRequest('POST', '/csrf-protected', server: ERROR_MAPPING_JSON));
    $wrong = $this->app->router->handle(integrationRequest('POST', '/csrf-protected', server: [
        ...ERROR_MAPPING_JSON,
        'HTTP_X_CSRF_TOKEN' => 'not-the-session-token',
    ]));

    expect($missing->statusCode())->toBe(419)
        ->and($wrong->statusCode())->toBe(419)
        ->and($wrong->body())->not->toContain('csrf ok');
})->issue(169);

it('answers unmatched requests with 404 and 405 without saving a session', function (): void {
    $notFound = $this->app->router->handle(integrationRequest('GET', '/no-such-page', server: ERROR_MAPPING_JSON));
    $notAllowed = $this->app->router->handle(integrationRequest('POST', '/health', server: ERROR_MAPPING_JSON));
    // Matched, but the route never touches the session.
    $missing = $this->app->router->handle(integrationRequest('GET', '/authors/999999', server: ERROR_MAPPING_JSON));
    $rows = $this->app->container->get(ConnectionInterface::class)->query('SELECT id FROM sessions');

    expect($notFound->statusCode())->toBe(404)
        ->and($notAllowed->statusCode())->toBe(405)
        ->and($notAllowed->headers()['Allow'])->toContain('GET')
        ->and($missing->statusCode())->toBe(404)
        ->and(integrationCookieValue($notFound, 'marko_session'))->toBeNull()
        ->and(integrationCookieValue($notAllowed, 'marko_session'))->toBeNull()
        ->and(integrationCookieValue($missing, 'marko_session'))->toBeNull()
        ->and($rows)->toBeEmpty();
})->issue(236);
