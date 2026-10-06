<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Authentication\AuthManager;
use Marko\Authentication\Exceptions\AuthException;
use Marko\Authorization\Attributes\Can;
use Marko\Authorization\Contracts\GateInterface;
use Marko\Authorization\Exceptions\AuthorizationException;
use Marko\Authorization\Exceptions\PolicyException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use Marko\RateLimiter\Attributes\RateLimit;
use Marko\RateLimiter\Middleware\RateLimitMiddleware;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;

readonly class IntegrationController
{
    public function __construct(
        private SessionInterface $session,
        private AuthManager $auth,
        private GateInterface $gate,
    ) {}

    #[Get('/health')]
    public function health(): Response
    {
        return new Response('ok');
    }

    /**
     * Counts visits in the session, so a second request carrying the session
     * cookie proves the database session handler persisted the first one.
     */
    #[Get('/visits')]
    public function visits(): Response
    {
        $visits = (int) $this->session->get('visits', 0) + 1;
        $this->session->set('visits', $visits);

        return new Response((string) $visits);
    }

    /**
     * Two requests per minute per client, counted in Redis through
     * marko/cache-redis (#165).
     */
    #[Get('/limited')]
    #[Middleware(RateLimitMiddleware::class)]
    #[RateLimit(maxAttempts: 2, decaySeconds: 60)]
    public function limited(): Response
    {
        return new Response('limited ok');
    }

    /**
     * Its own limit and its own counter: exhausting /limited must not deny
     * this route (#165).
     */
    #[Get('/limited/other')]
    #[Middleware(RateLimitMiddleware::class)]
    #[RateLimit(maxAttempts: 3, decaySeconds: 60)]
    public function limitedOther(): Response
    {
        return new Response('other limited ok');
    }

    /**
     * Logs in fixture user 1 through the default (session) guard.
     *
     * @throws AuthException|ConfigNotFoundException
     */
    #[Get('/login')]
    public function login(): Response
    {
        $this->auth->guard()->loginById(1);

        return new Response('logged in');
    }

    /**
     * The gate denies `view-admin` to everyone (see module.php), so any
     * authenticated request must get a 403 and a guest a 401. #[Can] is
     * enforced by the global AuthorizationMiddleware (#167); the route
     * declares no middleware of its own.
     */
    #[Get('/admin')]
    #[Can('view-admin')]
    public function admin(): Response
    {
        return new Response('admin area');
    }

    /**
     * The imperative check: no #[Can], so the middleware lets the request
     * through and Gate::authorize() itself denies `view-admin`. The thrown
     * AuthorizationException must render as 403, not 500 (#222).
     *
     * @throws AuthorizationException|PolicyException
     */
    #[Get('/admin/report')]
    public function adminReport(): Response
    {
        $this->gate->authorize('view-admin');

        return new Response('admin report');
    }
}
