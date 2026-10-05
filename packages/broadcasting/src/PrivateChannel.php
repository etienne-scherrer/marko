<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Override;

/**
 * A private channel: subscribers must be authorized by a #[BroadcastChannel] authorizer.
 */
readonly class PrivateChannel extends Channel
{
    #[Override]
    public function isPrivate(): bool
    {
        return true;
    }
}
