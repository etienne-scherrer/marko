<?php

declare(strict_types=1);

use Marko\Authentication\AuthManager;
use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\AuthenticationToken\Guard\TokenGuard;
use Marko\AuthenticationToken\Service\TokenManager;
use Marko\Core\RequestStateResetter;
use Marko\Integration\Fixture\Auth\AuthEventLog;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;

/*
 * Remember-me cookies and authentication events through the real router,
 * session middleware and queued-cookie middleware (#168), and bearer tokens
 * through marko/authentication-token's guard driver (#232).
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

it('authenticates a bearer token per request through the token guard driver', function (): void {
    $user = $this->app->container->get(UserProviderInterface::class)->retrieveById(1)
        ?? throw new RuntimeException('The fixture user provider has no user 1.');
    $tokens = $this->app->container->get(TokenManager::class);
    $valid = $tokens->createToken($user, 'integration')->plainTextToken;
    $expired = $tokens->createToken($user, 'expired', expiresAt: new DateTimeImmutable('-1 hour'))->plainTextToken;
    $bearer = static fn (string $token): array => ['HTTP_AUTHORIZATION' => "Bearer $token"];

    $authenticated = nextAuthRequest($this, integrationRequest('GET', '/token/whoami', server: $bearer($valid)));
    // The same worker, the next request: AuthManager's cached token guard
    // must not answer with the previous request's user.
    $anonymous = nextAuthRequest($this, integrationRequest('GET', '/token/whoami'));
    $rejected = nextAuthRequest($this, integrationRequest('GET', '/token/whoami', server: $bearer($expired)));
    $again = nextAuthRequest($this, integrationRequest('GET', '/token/whoami', server: $bearer($valid)));

    expect($this->app->container->get(AuthManager::class)->guard('api'))->toBeInstanceOf(TokenGuard::class)
        ->and($authenticated->body())->toBe('1')
        ->and($anonymous->body())->toBe('guest')
        ->and($rejected->body())->toBe('guest')
        ->and($again->body())->toBe('1')
        ->and(integrationCookieValue($authenticated, 'marko_session'))->toBeNull();
})->issue(232);

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
