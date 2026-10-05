<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Attributes;

use Attribute;

/**
 * Registers a ChannelAuthorizerInterface implementation for a private channel pattern.
 *
 * Patterns match dot-separated channel names; `{name}` captures one segment and is passed
 * to the authorizer as `$params['name']`, e.g. `shows.{showId}` matches `shows.42`.
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class BroadcastChannel
{
    public function __construct(
        public string $pattern,
    ) {}
}
