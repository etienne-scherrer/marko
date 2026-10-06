<?php

declare(strict_types=1);

namespace Marko\Broadcasting\Tests\Fixtures\PresenceModule;

use Marko\Authentication\AuthenticatableInterface;
use Marko\Broadcasting\Attributes\BroadcastChannel;
use Marko\Broadcasting\PresenceChannelAuthorizerInterface;
use Marko\Broadcasting\PresenceMember;

/** @noinspection PhpUnused - discovered via #[BroadcastChannel] */
#[BroadcastChannel('rooms.{roomId}')]
class RoomPresenceAuthorizer implements PresenceChannelAuthorizerInterface
{
    public function authorize(
        ?AuthenticatableInterface $user,
        array $params,
    ): ?PresenceMember {
        if ($user === null) {
            return null;
        }

        return new PresenceMember(
            id: $user->getAuthIdentifier(),
            info: ['room' => $params['roomId']],
        );
    }
}
