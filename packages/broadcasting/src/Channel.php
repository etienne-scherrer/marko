<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Broadcasting\Exceptions\BroadcastException;

/**
 * A public channel: anyone may subscribe without authorization.
 */
readonly class Channel
{
    /**
     * @throws BroadcastException
     */
    public function __construct(
        public string $name,
    ) {
        if ($this->name === '') {
            throw BroadcastException::emptyChannelName();
        }
    }

    /**
     * Normalize a channel argument: plain strings become public channels.
     *
     * @throws BroadcastException
     */
    public static function from(string|self $channel): self
    {
        return $channel instanceof self ? $channel : new self($channel);
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPresence(): bool
    {
        return false;
    }
}
