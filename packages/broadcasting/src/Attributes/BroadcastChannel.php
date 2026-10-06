<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Attributes;

use Attribute;

/**
 * Registers an authorizer for a private or presence channel pattern: a ChannelAuthorizerInterface
 * implementation serves private channels, a PresenceChannelAuthorizerInterface implementation serves
 * presence channels.
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
