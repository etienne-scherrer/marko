<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Authentication\AuthManager;
use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Authentication\Exceptions\AuthException;
use Marko\Config\Exceptions\ConfigNotFoundException;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;
use Random\RandomException;
use RuntimeException;

/**
 * Remember-me and the authentication events through the default (session)
 * guard (#168). GET keeps the CSRF middleware out of the way.
 */
readonly class AuthController
{
    public function __construct(
        private AuthManager $auth,
        private UserProviderInterface $userProvider,
    ) {}

    /**
     * Logs in fixture user 1 with remember-me, which queues the
     * remember_session cookie.
     *
     * @throws AuthException|ConfigNotFoundException|RandomException
     */
    #[Get('/login/remember')]
    public function loginRemembered(): Response
    {
        $user = $this->userProvider->retrieveById(1)
            ?? throw new RuntimeException('The fixture user provider has no user 1.');

        $this->auth->guard()->login($user, remember: true);

        return new Response('logged in, remembered');
    }

    /**
     * @throws AuthException|ConfigNotFoundException|RandomException
     */
    #[Get('/logout')]
    public function logout(): Response
    {
        $this->auth->guard()->logout();

        return new Response('logged out');
    }

    /**
     * The authenticated user's id, or "guest".
     *
     * @throws AuthException|ConfigNotFoundException|RandomException
     */
    #[Get('/whoami')]
    public function whoami(): Response
    {
        return new Response((string) ($this->auth->guard()->id() ?? 'guest'));
    }
}
