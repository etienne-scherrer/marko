<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Broadcasting\Exceptions\BroadcastException;

/**
 * A subscriber admitted to a presence channel, with the public info shared with other members.
 */
readonly class PresenceMember
{
    /**
     * @param array<string, scalar|null> $info
     *
     * @throws BroadcastException
     */
    public function __construct(
        public string|int $id,
        public array $info = [],
    ) {
        if ($this->id === '') {
            throw BroadcastException::emptyPresenceMemberId();
        }
    }
}
