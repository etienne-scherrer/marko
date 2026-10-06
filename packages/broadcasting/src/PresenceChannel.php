<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Override;

/**
 * A presence channel: authorized like a private channel, and members can see who else is subscribed.
 */
readonly class PresenceChannel extends Channel
{
    #[Override]
    public function isPrivate(): bool
    {
        return true;
    }

    #[Override]
    public function isPresence(): bool
    {
        return true;
    }
}
