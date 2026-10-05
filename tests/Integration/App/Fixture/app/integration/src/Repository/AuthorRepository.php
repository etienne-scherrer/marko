<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Repository;

use Marko\Database\Repository\Repository;
use Marko\Integration\Fixture\Entity\Author;

/**
 * @extends Repository<Author>
 */
class AuthorRepository extends Repository
{
    protected const string ENTITY_CLASS = Author::class;
}
