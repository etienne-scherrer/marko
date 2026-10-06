# Devil's Advocate Review: clock-adoption-database-queue

## Critical (Must fix before building)
None. The container claim in Discovery Notes holds: `Container::resolve()` resolves class-typed params via `resolve($typeName)` with no null short-circuit, so a trailing `?ClockInterface $clock = null` on `Repository` gets the bound clock when autowired.

## Important (Should fix before building)
1. **003: `module.php` factory will not autowire the clock.** `QueueInterface` is bound to a closure that calls `new DatabaseQueue(...)` with named args. A new required param has to be passed explicitly (`clock: $container->get(ClockInterface::class)`). It also has to come before `$table = 'jobs'`, because PHP deprecates required-after-optional. Call sites to update: `DatabaseQueueTest`, `DatabaseQueueAttemptsTest`, `Integration/PgSqlRoundTripTest`. The real-driver suites now run in CI, so all three have to change.
2. **003: the exhausted-attempts `failedAt` is untested.** `reserveNext()` passes `$now` to `failExhaustedJob()`, which becomes the `FailedJob::failedAt`. Add a test so the fifth `now()` consumer is covered.
3. **004: the `FileTaskMutex` factory in `scheduler/module.php` uses `new FileTaskMutex(directory: ...)`**, so the clock has to be passed explicitly. The `ScheduleWorkCommand` loop re-reads the clock until `secondsUntilNextRun <= 0`. With a FakeClock, a test sleeper that does not `travel()` the clock loops forever. Spell out that the test sleeper must advance the FakeClock. `FileTaskMutex::now()` returns an int, so it should use `$clock->now()->getTimestamp()`.
4. **001: `MigrationGenerator` currently uses `date('YmdHis', time() + $offset)`.** A naive port that keeps `date(...)` fails the plan's own success-criteria grep. Specify `$this->clock->now()->modify("+$offset seconds")->format('YmdHis')`. Call sites: `tests/Migration/Helpers.php:279`, `MigrationGeneratorTest.php:105`, `Feature/EntityToMigrationWorkflowTest.php:191`.
5. **001: the UTC conversion is not proven.** `FakeClock('...')` uses the PHP default timezone. If the test environment is UTC, the "in UTC" test passes even when the conversion is missing. The test has to use a non-UTC instant (e.g. `+02:00`) and assert UTC output. `Repository::now()` must also stay `protected` and overridable, because `database.md:353` documents overriding it.
6. **002: parameter order.** The `Worker` clock has to go before `BackoffValidator $backoffValidator = new BackoffValidator()`. `WorkerInterface => Worker::class` autowires, so `module.php` needs no change. Some call sites are in other groups' packages (`notification/tests/Unit/SerializableNotificationJobTest.php`, `webhook/tests/Jobs/SerializableWebhookJobTest.php`). Use a one-line named `clock:` arg there to keep merge conflicts with the parallel notification group small.
7. **005: `database.md:353` says to "override it in a repository to supply a different clock".** This now needs to describe the injected clock, with `now()` override as the secondary option.

## Minor (Nice to address)
- queue-database stores `Y-m-d H:i:s` in the clock's timezone. A `SystemClock` configured with a non-default timezone changes the stored wall-clock strings compared with today's default-tz behaviour. Rows stay consistent as long as web and worker share config. Worth a docs note.
- Scheduler source in this worktree already looks partially migrated (`ScheduleWorkCommand` takes `ClockInterface`, tests use `FakeClock`). Workers should check the current state before starting 004.

## Questions for the Team
- Should queue-database normalise stored times to UTC, like `Repository::now()`, instead of using the clock's timezone? That would be a behaviour change for existing rows, so it is left as is here.
