<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

readonly class ChannelDefinition
{
    /**
     * @param class-string<ChannelAuthorizerInterface> $authorizerClass
     */
    public function __construct(
        public string $pattern,
        public string $authorizerClass,
    ) {}

    /**
     * Match a channel name against the pattern.
     *
     * @return array<string, string>|null Captured placeholder values, or null when the name does not match
     */
    public function match(string $channelName): ?array
    {
        $regex = preg_replace_callback(
            '/\\\\\{([A-Za-z_][A-Za-z0-9_]*)\\\\}/',
            fn (array $matches): string => '(?P<' . $matches[1] . '>[^.]+)',
            preg_quote($this->pattern, '#'),
        );

        if (preg_match('#^' . $regex . '$#', $channelName, $matches) !== 1) {
            return null;
        }

        return array_filter($matches, is_string(...), ARRAY_FILTER_USE_KEY);
    }
}
