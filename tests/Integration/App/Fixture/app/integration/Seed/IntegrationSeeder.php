<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Seed;

use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;
use Marko\Integration\Fixture\Entity\Author;
use Marko\Integration\Fixture\Entity\Book;
use Marko\Integration\Fixture\Repository\AuthorRepository;
use Marko\Integration\Fixture\Repository\BookRepository;

#[Seeder(name: 'integration')]
readonly class IntegrationSeeder implements SeederInterface
{
    public function __construct(
        private AuthorRepository $authors,
        private BookRepository $books,
    ) {}

    public function run(): void
    {
        $author = new Author();
        $author->name = 'Seeded Author';
        $this->authors->save($author);

        $book = new Book();
        $book->authorId = (int) $author->id;
        $book->title = 'Seeded Book';
        $this->books->save($book);
    }
}
