<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Observer;

use Marko\Core\Attributes\Observer;
use Marko\Integration\Fixture\Event\BookPublished;

/**
 * Declared async: dispatching BookPublished should push a job to the queue
 * rather than write the marker during the dispatch call (#163).
 */
#[Observer(event: BookPublished::class, async: true)]
readonly class RecordBookPublished
{
    public function handle(BookPublished $event): void
    {
        file_put_contents($event->markerPath, 'observer ran');
    }
}
