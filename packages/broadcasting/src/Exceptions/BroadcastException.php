<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Exceptions;

use Marko\Core\Exceptions\MarkoException;
use Throwable;

class BroadcastException extends MarkoException
{
    public static function emptyChannelName(): self
    {
        return new self(
            message: 'Channel name must not be empty.',
            context: 'While creating a broadcasting Channel',
            suggestion: "Pass a non-empty name such as 'orders.42' to Channel or PrivateChannel.",
        );
    }

    public static function emptyEventName(): self
    {
        return new self(
            message: 'Broadcast event name must not be empty.',
            context: 'While broadcasting an event',
            suggestion: "Pass a non-empty event name such as 'order.shipped'.",
        );
    }

    public static function unencodablePayload(
        string $event,
        string $reason,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: "Payload for broadcast event '$event' cannot be encoded as JSON.",
            context: "json_encode failed: $reason",
            suggestion: 'Broadcast only JSON-serializable data: scalars, arrays and JsonSerializable objects.',
            previous: $previous,
        );
    }

    public static function publishFailed(
        string $driver,
        string $channel,
        string $reason,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: "Failed to broadcast to channel '$channel' via $driver.",
            context: "The $driver request for channel '$channel' failed: $reason",
            suggestion: "Check that the $driver server is reachable and that the credentials in config/broadcasting-" . strtolower(
                $driver,
            ) . '.php are correct.',
            previous: $previous,
        );
    }

    public static function invalidChannelName(
        string $driver,
        string $channel,
        string $allowed,
    ): self {
        return new self(
            message: "Channel name '$channel' is not valid for $driver.",
            context: "$driver channel names may only contain $allowed",
            suggestion: "Rename the channel so it only uses $allowed, e.g. 'orders.42'.",
        );
    }
}
