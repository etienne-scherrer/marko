<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Exceptions;

class ChannelAuthorizationException extends BroadcastException
{
    public static function unknownChannel(string $channelName): self
    {
        return new self(
            message: "No authorizer is registered for private channel '$channelName'.",
            context: "While authorizing a subscription to private channel '$channelName'",
            suggestion: "Create a class implementing ChannelAuthorizerInterface and mark it with #[BroadcastChannel('...')] "
                . 'using a pattern that matches this channel (placeholders like {id} match one dot-separated segment). '
                . 'Private channels are never allowed by default.',
        );
    }

    public static function notAnAuthorizer(string $className): self
    {
        return new self(
            message: "Class '$className' has #[BroadcastChannel] but must implement ChannelAuthorizerInterface.",
            context: 'While discovering #[BroadcastChannel] authorizers',
            suggestion: "Add 'implements ChannelAuthorizerInterface' to $className and implement authorize().",
        );
    }

    /**
     * @param list<string> $classNames
     */
    public static function duplicatePattern(
        string $pattern,
        array $classNames,
    ): self {
        $classList = implode(', ', $classNames);

        return new self(
            message: "Channel pattern '$pattern' is registered twice: $classList",
            context: 'While discovering #[BroadcastChannel] authorizers',
            suggestion: 'Keep a single authorizer per pattern. To replace a vendor authorizer, use #[Preference] on a subclass instead of registering the pattern again.',
        );
    }
}
