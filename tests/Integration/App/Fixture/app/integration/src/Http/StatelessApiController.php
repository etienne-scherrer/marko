<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\RoutePrefix;
use Marko\Routing\Attributes\WithoutMiddleware;
use Marko\Routing\Http\Response;
use Marko\Routing\UrlGeneratorInterface;
use Marko\Session\Middleware\SessionMiddleware;

/**
 * A stateless JSON API: prefixed, named routes that skip the global session
 * middleware registered by marko/session-database.
 */
#[RoutePrefix('/api', namePrefix: 'api.')]
#[WithoutMiddleware(SessionMiddleware::class)]
readonly class StatelessApiController
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    /** @noinspection PhpUnused - Invoked by the router */
    #[Get('/status/{code:\d+}', name: 'status')]
    public function status(
        int $code,
    ): Response {
        return Response::json([
            'code' => $code,
            'self' => $this->urlGenerator->route('api.status', ['code' => $code]),
        ]);
    }
}
