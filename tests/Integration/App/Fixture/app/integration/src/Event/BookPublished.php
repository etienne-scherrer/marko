<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Event;

use Marko\Core\Event\Event;

class BookPublished extends Event
{
    public function __construct(
        public readonly string $markerPath,
    ) {}
}
