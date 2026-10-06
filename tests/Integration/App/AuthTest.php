<?php

declare(strict_types=1);

use Marko\Core\RequestStateResetter;
use Marko\Integration\Fixture\Auth\AuthEventLog;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;

/*
 * Remember-me cookies and authentication events through the real router,
 * session middleware and queued-cookie middleware (#168).
 */

pest()->group('integration-services');

beforeEach(fn () => setUpIntegrationTest($this));

afterEach(fn () => tearDownIntegrationTest($this));

/**
 * Handle $request as the next request of a long-running worker: state the
 * previous request left behind (the guard's cached user, the session, the
 * cookie jar) is reset first, so only what the request carries counts.
 */
function nextAuthRequest(
    object $test,
    Request $request,
): Response {
    $test->app->container->get(RequestStateResetter::class)->reset();

    return $test->app->router->handle($request);
}

it('issues a remember-me cookie that re-authenticates a later request', function (): void {
    $login = nextAuthRequest($this, integrationRequest('GET', '/login/remember'));
    $remember = integrationCookieValue($login, 'remember_session');

    // Only the remember cookie: no session cookie, so the session is new.
    $remembered = nextAuthRequest(
        $this,
        integrationRequest('GET', '/whoami', cookies: ['remember_session' => (string) $remember]),
    );
    $guest = nextAuthRequest($this, integrationRequest('GET', '/whoami'));
    $forged = nextAuthRequest(
        $this,
        integrationRequest('GET', '/whoami', cookies: ['remember_session' => '1|' . bin2hex(random_bytes(32))]),
    );

    expect($login->body())->toBe('logged in, remembered')
        ->and($remember)->toStartWith('1|')
        ->and($remembered->body())->toBe('1')
        ->and($guest->body())->toBe('guest')
        ->and($forged->body())->toBe('guest');
})->issue(168);

it('dispatches login and logout events', function (): void {
    $login = nextAuthRequest($this, integrationRequest('GET', '/login/remember'));
    $sessionId = (string) integrationCookieValue($login, 'marko_session');

    $logout = nextAuthRequest(
        $this,
        integrationRequest('GET', '/logout', cookies: ['marko_session' => $sessionId]),
    );

    expect($logout->body())->toBe('logged out')
        ->and($this->app->container->get(AuthEventLog::class)->entries)->toBe([
            'login 1 via session (remember)',
            'logout 1 via session',
        ]);
})->issue(168);
