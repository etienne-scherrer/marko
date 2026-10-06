<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Authentication\AuthenticatableInterface;

interface ChannelAuthorizerInterface
{
    /**
     * Decide whether the user may subscribe to a private channel matching this authorizer's pattern.
     *
     * @param AuthenticatableInterface|null $user The current user, or null for guests
     * @param array<string, string> $params Values captured by the `{name}` placeholders in the pattern
     */
    public function authorize(
        ?AuthenticatableInterface $user,
        array $params,
    ): bool;
}
