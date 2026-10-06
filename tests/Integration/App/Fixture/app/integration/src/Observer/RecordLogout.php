<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Observer;

use Marko\Authentication\Event\LogoutEvent;
use Marko\Core\Attributes\Observer;
use Marko\Integration\Fixture\Auth\AuthEventLog;

#[Observer(event: LogoutEvent::class)]
readonly class RecordLogout
{
    public function __construct(
        private AuthEventLog $authEventLog,
    ) {}

    public function handle(
        LogoutEvent $event,
    ): void {
        $this->authEventLog->record("logout {$event->user->getAuthIdentifier()} via $event->guard");
    }
}
