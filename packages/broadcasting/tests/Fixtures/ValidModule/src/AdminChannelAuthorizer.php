<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Tests\Fixtures\ValidModule;

use Marko\Authentication\AuthenticatableInterface;
use Marko\Broadcasting\Attributes\BroadcastChannel;
use Marko\Broadcasting\ChannelAuthorizerInterface;

/** @noinspection PhpUnused - discovered via #[BroadcastChannel] */
#[BroadcastChannel('admin')]
class AdminChannelAuthorizer implements ChannelAuthorizerInterface
{
    public function authorize(
        ?AuthenticatableInterface $user,
        array $params,
    ): bool {
        return $user?->getAuthIdentifier() === 1;
    }
}
