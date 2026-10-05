<?php

declare(strict_types=1);

use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Authorization\Contracts\GateInterface;
use Marko\Core\Path\ProjectPaths;
use Marko\Scheduler\Schedule;
use Marko\Testing\Fake\FakeAuthenticatable;
use Marko\Testing\Fake\FakeUserProvider;

return [
    'bindings' => [
        // One known user (id 1) for the session guard; the fixture has no
        // users table because authentication itself is not under test here.
        UserProviderInterface::class => static fn (): UserProviderInterface => new FakeUserProvider(
            users: [1 => new FakeAuthenticatable(id: 1)],
        ),
    ],
    'boot' => static function (Schedule $schedule, GateInterface $gate, ProjectPaths $paths): void {
        // Registered the way the scheduler docs describe. `schedule:run` should
        // find and run it; today a different Schedule instance is injected
        // into the command, so it finds nothing (#164).
        $schedule->call(static function () use ($paths): void {
            file_put_contents($paths->base . '/storage/scheduled-task.txt', 'scheduled task ran');
        })->everyMinute()->description('integration heartbeat');

        $gate->define('view-admin', static fn (): bool => false);
    },
];
