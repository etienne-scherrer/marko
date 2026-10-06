<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Authentication\AuthenticatableInterface;

interface PresenceChannelAuthorizerInterface
{
    /**
     * Decide whether the user may join a presence channel matching this authorizer's pattern.
     *
     * @param AuthenticatableInterface|null $user The current user, or null for guests
     * @param array<string, string> $params Values captured by the `{name}` placeholders in the pattern
     *
     * @return PresenceMember|null The member to announce to the channel, or null to deny
     */
    public function authorize(
        ?AuthenticatableInterface $user,
        array $params,
    ): ?PresenceMember;
}
