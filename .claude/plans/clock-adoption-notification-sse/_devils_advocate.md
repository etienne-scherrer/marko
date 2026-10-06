# Devil's Advocate Review: clock-adoption-notification-sse

## Critical (Must fix before building)
None. Every wall-clock read listed in Discovery matches the source on develop (verified with grep), and no `src/` outside these packages constructs the affected classes by hand (only `AmphpSseServer` builds `ReplayBuffer`).

## Important (Should fix before building)
1. **Task 001: notification-database has no `marko/testing` in `require-dev`.** `packages/notification-database/composer.json` lists only `pestphp/pest`, but the new tests use `FakeClock`. That works in the monorepo through the root autoloader and breaks if the package is installed on its own. Fix: add `"marko/testing": "self.version"` to `require-dev` (the other six packages already have it).
2. **Task 002: some test constructions are easy to miss and will fatal at runtime instead of failing an assertion.**
   - `tests/Feature/AmphpSseServerTest.php` defines an anonymous subclass of `AmphpSseServer` with its own constructor that calls `parent::__construct($config, $subscriber, $signature, $logger)`. That constructor has to accept and forward the clock.
   - `tests/Unit/Server/ChannelHubTest.php` calls `new ReplayBuffer(size: 100, ttl: 300)` with no clock. Once the clock is required, that call fails.
   - `tests/Feature/RedisLiveTest.php` and `ServeCommandTest.php` construct `AmphpSignature`/`AmphpSseServer`. RedisLiveTest is `integration-destructive`, so `composer test` skips it and only `composer test:all` or CI catches it.
   Fix: list these files in the task context.
3. **Task 002: id format.** `generateId` uses `sprintf('%013d-…', (int) floor(microtime(true) * 1000))`. `format('Uv')` returns a string, and `%013d` must still get an int so the zero-padding and lexical ordering stay the same. Fix: say this in the task.

## Minor (Nice to address)
- Task 001 already has `DatabaseChannelClockTest.php` and `DatabaseNotificationRepositoryClockTest.php` in the worktree. The worker should extend them, not duplicate them.
- Task 005: the test "reads the system clock when no clock is passed" cannot observe time without sleeping. The cleanest check is that a stream built without a clock still iterates and times out (for example with `timeout: 0`), not reflection on a private property.
- Task 005: `SseStream` is a `readonly class`. A promoted `private ClockInterface $clock = new SystemClock()` is valid (new-in-initializer, PHP 8.1+), but each stream gets its own `SystemClock` instance rather than the container singleton. That is harmless.
- Task 006: `sse` README constructs `SseStream`. The clock parameter is optional, so the README does not need to change.

## Questions for the Team
- Does the `SseStream` optional-default exception (as opposed to the required parameter used everywhere else) need to be mentioned in the PR body as a deliberate deviation from the #200 pattern?
