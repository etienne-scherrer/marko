<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Repository;

use Marko\Database\Repository\Repository;
use Marko\Integration\Fixture\Entity\Book;

/**
 * @extends Repository<Book>
 */
class BookRepository extends Repository
{
    protected const string ENTITY_CLASS = Book::class;
}
