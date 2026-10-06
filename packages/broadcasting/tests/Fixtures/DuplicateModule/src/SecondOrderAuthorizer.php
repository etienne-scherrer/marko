<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Tests\Fixtures\DuplicateModule;

use Marko\Authentication\AuthenticatableInterface;
use Marko\Broadcasting\Attributes\BroadcastChannel;
use Marko\Broadcasting\ChannelAuthorizerInterface;

/** @noinspection PhpUnused - discovered via #[BroadcastChannel] */
#[BroadcastChannel('orders.{orderId}')]
class SecondOrderAuthorizer implements ChannelAuthorizerInterface
{
    public function authorize(
        ?AuthenticatableInterface $user,
        array $params,
    ): bool {
        return true;
    }
}
