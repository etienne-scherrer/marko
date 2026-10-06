# Devil's Advocate Review: queue-database-utc-timestamps

## Critical (Must fix before building)

- **Task 001 - name collision.** `DatabaseTimezoneConfig` already has `private static function parse(mixed $name): DateTimeZone`. A public instance `parse(string): DateTimeImmutable` is a fatal redeclaration. Fix: rename the private static to `resolveTimezone()` (constructor and `fromName()` call sites). (The worktree's in-progress code already does this; the task now says so explicitly.)

## Important (Should fix before building)

- **All tasks - constructor parameter position unspecified.** Docs call constructors positionally: `testing.md:184` `new TokenGuard($repository, $currentRequest, $clock, $userProvider)` and `queue-database.md:97` `new DatabaseQueue($connection, $envelope, $failedJobs, $queryBuilderFactory, $clock)`. Parallel workers placing the new param differently make docs/examples inconsistent. Fix: rule pinned in `_plan.md` - insert `DatabaseTimezoneConfig $databaseTimezoneConfig` immediately after `ClockInterface $clock` (after `$connection` in `DatabaseFailedJobRepository`, which has no clock); task 008 updates both positional examples.
- **Tasks 002, 005 - test containers cannot autowire the config.** `DatabaseTimezoneConfig` needs `ProjectPaths`, which only `Application` binds. `queue-database/tests/ModuleTest.php` and `authentication-token/tests/Feature/TokenGuardWiringTest.php` use a bare `new Container()`, so `get()` fails. Fix: bind `DatabaseTimezoneConfig::fromName(...)` as an instance in those containers.
- **Tasks 002/003 - parallel edits to the same files.** Task 003 changes `DatabaseFailedJobRepository`'s constructor, which breaks `DatabaseQueueAttemptsTest`, `PgSqlRoundTripTest` and the new queue timezone test - all files task 002 is editing. Fix: 003 now depends on 002.
- **Task 002 - DST regression can pass without the fix.** With the default `retryAfter` of 90, a reservation at 01:50 EDT becomes due at 01:51:30 EDT, before the fall-back, so local-wall formatting still passes. Fix: the test must cross the transition, e.g. `retryAfter: 900`, reserve at 01:50 EDT (05:50Z), assert reclaimable at 01:05 EST (06:05Z). Old code compares cutoff "00:50" against "01:50" and fails. The mixed-process test likewise needs a delayed job and an assertion that it is not popped early and is popped at the due instant.
- **Task 004 - MySQL test has no package dependency.** `queue-database/composer.json` require-dev lacks `marko/database-mysql`. Fix: add it, and use `IntegrationDatabase::config()` (the `MARKO_TEST_MYSQL_*` vars, which throw under `MARKO_INTEGRATION_REQUIRED`) instead of `DB_HOST`.
- **Task 008 - consumer-visible changes not documented.** `queue:failed` (`FailedCommand` formats `failedAt`) now shows database-zone times. `PersonalAccessToken::$expiresAt`/`$createdAt`, `DatabaseNotification::$createdAt`/`$readAt` and `WebhookAttempt::$attemptedAt` strings are now database-zone wall times, not local. During upgrade, old and new rows mix in `ORDER BY created_at` / `failed_at`. Fix: added to the task 008 requirements.

## Minor (Nice to address)

- `marko/notification` lists `marko/database` only under `suggest`. `DatabaseChannel` already imports `ConnectionInterface`, so the new import doesn't change the situation. Standalone package tests would need `marko/database` in require-dev.
- `DateTimeCast` could delegate to `format()`/`parse()` to keep one conversion. It is optional, since the cast still accepts a null config.
- `FakeClock` has no zone parameter. Tests build it with `new DateTimeImmutable('...', new DateTimeZone('America/New_York'))`.

## Questions for the Team

- MySQL `TIMESTAMP` columns with a DST-observing session `time_zone` (SYSTEM on a non-UTC server) shift UTC wall times that fall in the local spring-forward gap. Should this release only document pinning `time_zone = '+00:00'` (current plan), or should the MySQL driver set it on connect?
