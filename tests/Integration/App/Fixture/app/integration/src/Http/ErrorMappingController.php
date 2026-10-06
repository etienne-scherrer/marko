<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Database\Exceptions\RepositoryException;
use Marko\Integration\Fixture\Repository\AuthorRepository;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Attributes\Post;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\Security\Middleware\CsrfMiddleware;
use Marko\Validation\Contracts\ValidatorInterface;
use Marko\Validation\Exceptions\ValidationException;

/**
 * Routes that throw the exceptions the routing pipeline maps to HTTP
 * responses (#169). None of them catches anything.
 */
readonly class ErrorMappingController
{
    public function __construct(
        private ValidatorInterface $validator,
        private AuthorRepository $authorRepository,
    ) {}

    /**
     * A failed validateOrFail() throws ValidationException: 422.
     *
     * @throws ValidationException
     */
    #[Post('/authors')]
    public function store(
        Request $request,
    ): Response {
        $this->validator->validateOrFail($request->post(), ['name' => ['required', 'min:3']]);

        return new Response('valid', 201);
    }

    /**
     * findOrFail() for a missing id throws EntityNotFoundException: 404.
     *
     * @throws RepositoryException
     */
    #[Get('/authors/{id:\d+}')]
    public function show(
        int $id,
    ): Response {
        $this->authorRepository->findOrFail($id);

        return new Response('found');
    }

    /**
     * A state-changing request without a valid CSRF token throws
     * CsrfTokenMismatchException in the middleware: 419.
     */
    #[Post('/csrf-protected')]
    #[Middleware(CsrfMiddleware::class)]
    public function csrfProtected(): Response
    {
        return new Response('csrf ok');
    }
}
