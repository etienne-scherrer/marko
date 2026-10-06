<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting;

/**
 * Records the entity classes the EntityCreated observer saw.
 */
class CreatedEntityLog
{
    /** @var list<string> */
    public array $created = [];
}
