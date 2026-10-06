<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Http;

use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Repository\AuthorRepository;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Response;

readonly class AuthorController
{
    public function __construct(
        private AuthorRepository $authorRepository,
    ) {}

    /**
     * Saves an author and does not catch anything, so a database constraint
     * violation reaches the routing pipeline's exception mapping (#177).
     * GET keeps the CSRF middleware out of the way.
     */
    #[Get('/authors/create/{name}')]
    public function create(
        string $name,
    ): Response {
        $author = new Author();
        $author->name = $name;
        $this->authorRepository->save($author);

        return new Response((string) $author->id, 201);
    }
}
