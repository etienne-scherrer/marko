<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Broadcasting\Exceptions\BroadcastException;

interface BroadcasterInterface
{
    /**
     * Send an event to everyone subscribed to the channel.
     *
     * A plain string is treated as a public Channel.
     *
     * @param array<string, mixed> $data JSON-serializable event payload
     * @param string|null $id Optional event id (used by drivers that support replay, e.g. Mercure)
     *
     * @throws BroadcastException
     */
    public function broadcast(
        string|Channel $channel,
        string $event,
        array $data,
        ?string $id = null,
    ): void;

    /**
     * Broadcast an event object to every channel it lists.
     *
     * @throws BroadcastException
     */
    public function dispatch(
        BroadcastableInterface $broadcastable,
    ): void;
}
