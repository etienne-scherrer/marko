<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Authorization\Attributes\Can;
use Marko\Authorization\Middleware\AuthorizationMiddleware;
use Marko\RateLimiter\Middleware\RateLimitMiddleware;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Http\Response;
use Marko\Session\Contracts\SessionInterface;

readonly class IntegrationController
{
    public function __construct(
        private SessionInterface $session,
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

    #[Get('/limited')]
    #[Middleware(RateLimitMiddleware::class)]
    public function limited(): Response
    {
        return new Response('limited ok');
    }

    /**
     * The gate denies `view-admin` to everyone (see module.php), so any
     * authenticated request must get a 403 (#167).
     */
    #[Get('/admin')]
    #[Can('view-admin')]
    #[Middleware(AuthorizationMiddleware::class)]
    public function admin(): Response
    {
        return new Response('admin area');
    }
}
