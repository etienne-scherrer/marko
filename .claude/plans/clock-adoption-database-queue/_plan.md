# Plan: Clock Adoption (database, queue, queue-database, scheduler)

## Created
2026-10-05

## Status
completed

## Objective
Read time through an injected PSR-20 `ClockInterface` in `marko/database`, `marko/queue`, `marko/queue-database` and `marko/scheduler`, replacing raw `time()`/`date()`/`new DateTimeImmutable()` reads and the scheduler's `Closure` clocks.

## Related Issues
Relates to #221 (this is the database/queue/queue-database/scheduler group; other groups ship separately)

## Discovery Notes
- `marko/clock` binds `Psr\Clock\ClockInterface` to `SystemClock` (singleton); `FakeClock` lives in `marko/testing`.
- #200 pattern (session): required constructor parameter, autowired.
- `Repository` is an abstract base that applications subclass and tests construct by hand in ~250 places (including other groups' packages such as admin-auth and notification-database). Its existing optional collaborators (`queryBuilderFactory`, `eventDispatcher`, `relationshipLoader`) are nullable trailing params. A required clock would have to go before them (PHP deprecates required-after-optional), breaking every positional construction. The container resolves a nullable parameter when its type is bound, so a trailing `ClockInterface $clock = new SystemClock()` still gets the bound clock when autowired (the container resolves class-typed parameters from bindings, ignoring the default).
- `MigrationGenerator`, `Worker`, `DatabaseQueue`, `FileTaskMutex`, `ScheduleWorkCommand`, `RunScheduleCommand` are framework-internal / container-built; a required clock is appropriate.
- Scheduler's `Closure` clocks are not documented in `scheduler.md`, so they are replaced outright (no deprecation period). The `$sleeper` closure on `ScheduleWorkCommand` is not a clock and stays.
- `DateTimeCast` and `DatabaseFailedJobRepository` parse stored strings, not wall-clock reads; out of scope.

## Scope

### In Scope
- database: `Repository::now()` via clock (UTC preserved); `MigrationGenerator` file timestamps via clock
- queue: `Worker` failedAt via clock
- queue-database: push/pop/size/release via clock
- scheduler: `FileTaskMutex`, `ScheduleWorkCommand`, `RunScheduleCommand` via `ClockInterface`; remove `Closure` clocks
- composer.json `require` of `marko/clock` in all four packages (and `marko/testing` dev where missing)
- docs pages for the four packages + `clock.md` adopting-packages list

### Out of Scope
- Other #221 groups (cache, ratelimiter, page-cache, auth, admin-auth, errors, log, notification, broadcasting, media, sse, testing)
- Monotonic elapsed-time measurements

## Success Criteria
- [ ] Grep `\btime\(\)|new DateTimeImmutable\(\)|microtime\(|\bdate\(` finds no wall-clock reads in the four packages' src
- [ ] Each package has a FakeClock test covering its time-dependent behaviour without sleeping
- [ ] Scheduler no longer accepts Closure clocks
- [ ] composer.json requires marko/clock; docs updated
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | database: Repository and MigrationGenerator read the clock | - | completed |
| 002 | queue: Worker records failedAt from the clock | - | completed |
| 003 | queue-database: DatabaseQueue reads the clock | - | completed |
| 004 | scheduler: replace Closure clocks with ClockInterface | - | completed |
| 005 | docs: package pages and clock.md | 001, 002, 003, 004 | completed |

## Architecture Notes
- Required `ClockInterface $clock` constructor parameter for framework-built classes; `Repository` takes an optional trailing `ClockInterface $clock = new SystemClock()` (documented deviation; the container always injects the bound clock).
- Keep `Repository::now()` returning UTC by converting the clock's instant.

## Risks & Mitigations
- Required constructor params break hand-constructed instances: update all call sites in the repo; call out in the PR.
- Shared files with other groups (`clock.md`): keep edits to a few appended list lines.
- Closure-built bindings (`queue-database/module.php`, `scheduler/module.php`) do not autowire, so they must pass `clock: $container->get(ClockInterface::class)` explicitly.
- New required clock params go before existing defaulted params (`Worker::$backoffValidator`, `DatabaseQueue::$table`, `ScheduleWorkCommand::$sleeper`).
- Worker call sites in notification/webhook tests belong to other groups: add only a named `clock:` arg.
