<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Repository;

use Marko\Database\Repository\Repository;
use Marko\Integration\DatabaseTesting\Entity\Show;

/**
 * @extends Repository<Show>
 */
class ShowRepository extends Repository
{
    protected const string ENTITY_CLASS = Show::class;
}
