# Devil's Advocate Review: clock-adoption-auth-log

## Critical (Must fix before building)

1. **Task 002: an existing test constructs events without a timestamp.** `packages/admin-auth/tests/Unit/Config/AdminAuthConfigTest.php:110-147` builds all seven events, including `AdminUserDeleted` and `PermissionsSynced`, without passing a timestamp. Once the timestamp is required, these calls fail. The task did not list this file.
2. **Tasks 003 and 004: FakeClock is not available to the test suites.** `errors-simple`, `errors-advanced` and `log-file` only have `pestphp/pest` in `require-dev`. Their new FakeClock tests need `marko/testing` added to `require-dev`.
3. **Task 003: the change to `ErrorReport::fromThrowable()` breaks about 60 test call sites.** The affected files are `errors/tests/Unit/ErrorReportTest.php` (13), `errors-simple/tests/Unit/Formatters/{BasicHtmlFormatterTest,TextFormatterTest}.php` (36), `errors-advanced/tests/Unit/PrettyHtmlFormatterTest.php` (9), and handler and feature tests. The required `ClockInterface` also breaks every `new SimpleErrorHandler(...)` and `new AdvancedErrorHandler(...)` in tests. `AdvancedErrorHandler` currently has only optional params, so `new AdvancedErrorHandler()` breaks too. The task did not list any of these files.
4. **Task 004: the readonly class blocks the planned rotation fallback.** `FileLogger` is a `readonly class`, and `$rotation` is promoted with a default. The planned `?RotationStrategyInterface $rotation = null` with a `?? new DailyRotation($clock)` fallback cannot be a promoted property. It must be declared, not promoted, and assigned in the constructor body. Separately, `FileLoggerTest` (13 constructions) and `DailyRotationTest` (9 constructions using `new DailyRotation(new DateTimeImmutable(...))`) need rewriting, and the task did not list them.

## Important (Should fix before building)

5. **Tasks 001, 003, 004: the position of the new parameter is not specified.** A required parameter cannot follow optional ones, and positional calls in tests would break silently in different ways. The plan should set the exact signatures (now applied to the task files).
6. **Task 001: an acceptance criterion is already met.** `marko/authentication` already requires `marko/clock`. About 60 test constructions of `RememberTokenManager` and `RequestCookieJar` need updating (`AuthManagerTest`, `SessionGuard*Test`, `Middleware/*Test`, Integration tests). Those files were not listed.
7. **Task 002: the test seam is not described.** Before the database group lands, `Repository::now()` returns real UTC time. Tests should override `now()` in an anonymous subclass to return a fixed instant. Also, events used to take the default PHP timezone and now take UTC. That change is acceptable but should be documented.
8. **Task 005: only one of two clock paths is tested.** There is no test for the fallback when no `ClockInterface` is bound, which is the actual change.
9. **Task 006: the docs list is incomplete.** It misses `packages/errors/README.md:19` (`fromThrowable` example), `docs/packages/roadrunner-state-leaks.md:51` (`RememberTokenManager` description says it holds "only the configured lifetime"), the `log-file.md` constructor table (no `$clock` row), and the `errors.md:199` signature.

## Minor (Nice to address)

- Fixture `composer.json` stubs for `marko/authentication` (testing http-app, roadrunner app) do not list `marko/clock`. The clock module is present in both fixture vendor dirs, so this works today.
- `AdminUserDeleted` and `PermissionsSynced` are never dispatched in `src/`. Only their signatures change, and no dispatch should be added (no pseudo-functionality).

## Questions for the Team

- `FileLoggerFactory` calls `new DailyRotation()` directly. That means the documented `#[Preference(replaces: DailyRotation::class)]` example in `log-file.md` and `guides/logging.md` never takes effect. Should the factory inject `DailyRotation` from the container (which autowires the clock and honours Preferences) instead of holding the clock and calling `new`? That is a behaviour fix beyond #221.
- Task 002 depends on `Repository::now()` keeping its name and visibility after the database group's change. Is the database group committed to keeping `now()` as the seam? If it inlines the clock, task 002 breaks at merge.
