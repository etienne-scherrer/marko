<?php

declare(strict_types=1);

namespace Marko\Integration\DatabaseTesting\Observer;

use Marko\Core\Attributes\Observer;
use Marko\Database\Events\EntityCreated;
use Marko\Integration\DatabaseTesting\CreatedEntityLog;

#[Observer(event: EntityCreated::class)]
readonly class RecordCreatedEntity
{
    public function __construct(
        private CreatedEntityLog $createdEntityLog,
    ) {}

    /** @noinspection PhpUnused - Invoked by the event dispatcher */
    public function handle(EntityCreated $event): void
    {
        $this->createdEntityLog->created[] = $event->entityClass;
    }
}
