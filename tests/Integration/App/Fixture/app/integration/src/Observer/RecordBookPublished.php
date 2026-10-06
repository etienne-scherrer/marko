<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Observer;

use Marko\Core\Attributes\Observer;
use Marko\Integration\Fixture\Event\BookPublished;

/**
 * Declared async: dispatching BookPublished pushes an AsyncObserverJob to the
 * queue, and the marker is only written when a worker runs that job.
 */
#[Observer(event: BookPublished::class, async: true)]
readonly class RecordBookPublished
{
    public function handle(BookPublished $event): void
    {
        file_put_contents($event->markerPath, 'observer ran');
    }
}
