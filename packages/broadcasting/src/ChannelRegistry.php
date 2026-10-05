<?php

declare(strict_types=1);

namespace Marko\Broadcasting;

use Marko\Authentication\AuthenticatableInterface;
use Marko\Broadcasting\Discovery\BroadcastChannelDiscovery;
use Marko\Broadcasting\Exceptions\ChannelAuthorizationException;
use Marko\Core\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Authorizes private channel subscriptions against the discovered #[BroadcastChannel] authorizers.
 *
 * Drivers call authorize() when they issue subscriber credentials. A channel with no matching
 * authorizer is denied loudly with an exception — private channels are never open by default.
 */
class ChannelRegistry
{
    /**
     * @var list<ChannelDefinition>|null
     */
    private ?array $definitions = null;

    public function __construct(
        private readonly BroadcastChannelDiscovery $broadcastChannelDiscovery,
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @throws ChannelAuthorizationException|ReflectionException|ContainerExceptionInterface|NotFoundExceptionInterface
     */
    public function authorize(
        string $channelName,
        ?AuthenticatableInterface $user,
    ): bool {
        foreach ($this->definitions() as $definition) {
            $params = $definition->match($channelName);

            if ($params === null) {
                continue;
            }

            /** @var ChannelAuthorizerInterface $authorizer */
            $authorizer = $this->container->get($definition->authorizerClass);

            return $authorizer->authorize($user, $params);
        }

        throw ChannelAuthorizationException::unknownChannel($channelName);
    }

    /**
     * @return list<string>
     * @throws ChannelAuthorizationException|ReflectionException
     */
    public function patterns(): array
    {
        return array_map(
            fn (ChannelDefinition $definition): string => $definition->pattern,
            $this->definitions(),
        );
    }

    /**
     * @return list<ChannelDefinition>
     * @throws ChannelAuthorizationException|ReflectionException
     */
    private function definitions(): array
    {
        return $this->definitions ??= $this->broadcastChannelDiscovery->discover();
    }
}
