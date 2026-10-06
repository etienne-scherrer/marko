<?php

declare(strict_types=1);

use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Authorization\Contracts\GateInterface;
use Marko\Core\Path\ProjectPaths;
use Marko\Integration\Fixture\Auth\AuthEventLog;
use Marko\Scheduler\Schedule;
use Marko\Testing\Fake\FakeAuthenticatable;
use Marko\Testing\Fake\FakeUserProvider;

return [
    'bindings' => [
        // One known user (id 1) for the session guard; the fixture has no
        // users table. FakeUserProvider keeps the user's remember token in
        // memory, so it is shared for the life of the app (#168).
        UserProviderInterface::class => static fn (): UserProviderInterface => new FakeUserProvider(
            users: [1 => new FakeAuthenticatable(id: 1)],
        ),
    ],
    'singletons' => [
        UserProviderInterface::class,
        AuthEventLog::class,
    ],
    'boot' => static function (Schedule $schedule, GateInterface $gate, ProjectPaths $paths): void {
        // Registered the way the scheduler docs describe; `schedule:run` gets
        // the same shared Schedule, so it finds and runs them (#164).
        $schedule->call(static function () use ($paths): void {
            file_put_contents($paths->base . '/storage/scheduled-task.txt', 'scheduled task ran');
        })->everyMinute()->description('integration heartbeat');

        // Overlap-protected: skipped while a previous run still holds its mutex.
        $schedule->call(static function () use ($paths): void {
            file_put_contents($paths->base . '/storage/exclusive-task.txt', 'exclusive task ran');
        })->everyMinute()->description('integration exclusive')->withoutOverlapping();

        $gate->define('view-admin', static fn (): bool => false);
    },
];
