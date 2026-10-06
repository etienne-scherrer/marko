<?php

declare(strict_types=1);

namespace Marko\Integration\Fixture\Observer;

use Marko\Authentication\Event\LoginEvent;
use Marko\Core\Attributes\Observer;
use Marko\Integration\Fixture\Auth\AuthEventLog;

#[Observer(event: LoginEvent::class)]
readonly class RecordLogin
{
    public function __construct(
        private AuthEventLog $authEventLog,
    ) {}

    public function handle(
        LoginEvent $event,
    ): void {
        $remember = $event->remember ? ' (remember)' : '';

        $this->authEventLog->record(
            "login {$event->user->getAuthIdentifier()} via $event->guard$remember",
        );
    }
}
