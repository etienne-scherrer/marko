<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

/**
 * An event object that can be broadcast explicitly via BroadcasterInterface::dispatch().
 *
 * Nothing is broadcast implicitly: implementing this interface does not hook into the event dispatcher.
 */
interface BroadcastableInterface
{
    /**
     * @return list<string|Channel>
     */
    public function channels(): array;

    public function event(): string;

    /**
     * @return array<string, mixed>
     */
    public function payload(): array;
}
