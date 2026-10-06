# Task 002: DatabaseQueue writes in the database zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Inject `DatabaseTimezoneConfig` into `DatabaseQueue` and format created_at, available_at, reserved_at, the reclaim cutoff and the size() cutoff through it. Wire it in module.php.

## Context
- Related files: packages/queue-database/src/DatabaseQueue.php, packages/queue-database/module.php, packages/queue-database/tests/*
- Use a FakeClock in America/New_York with a UTC database zone; set/restore date_default_timezone_set() where the default matters. FakeClock has no zone argument: pass `new DateTimeImmutable('...', new DateTimeZone('America/New_York'))`.
- Constructor: insert `DatabaseTimezoneConfig $databaseTimezoneConfig` immediately after `ClockInterface $clock` (before the optional `table`).
- `tests/ModuleTest.php` uses a bare `new Container()` with no `ProjectPaths`, so autowiring `DatabaseTimezoneConfig` fails there: bind `DatabaseTimezoneConfig::fromName('UTC')` as an instance.
- DST test must cross the transition or it passes without the fix: use `retryAfter: 900`, reserve at 2026-11-01 01:50 EDT (05:50Z), assert not reclaimable at 01:59 EDT and reclaimable at 01:05 EST (06:05Z).
- Mixed-process test: push a delayed job (e.g. `later(60, ...)`) from the New York-clock queue, then assert the UTC-clock queue does not pop it before the due instant and pops it at the due instant.

## Requirements (Test Descriptions)
- [x] `it writes created_at and available_at in the database timezone whatever the clock timezone`
- [x] `it compares the pop and reclaim cutoffs in the database timezone`
- [x] `it counts available jobs against a cutoff in the database timezone`
- [x] `it makes a reservation taken at 01:50 EDT on a fall-back day reclaimable retry_after seconds later`
- [x] `it pops on time a job pushed by a queue whose clock is America/New_York from a queue whose clock is UTC`
- [x] `it wires the database timezone config into the bound queue`

## Acceptance Criteria
- All requirements have passing tests
- Existing queue-database tests updated for the new dependency

## Implementation Notes
DatabaseQueue takes `DatabaseTimezoneConfig` after the clock; module.php wires it. Found while going red-green: `modify("-90 seconds")` on a DST zone wall clock lands an hour off across fall-back, so delays and the reclaim cutoff now use Unix-timestamp arithmetic (`secondsAfter()`). The DST test reserves at 01:59 EDT and checks reclaim at 01:00:29 / 01:00:31 EST with the default retry_after. Added a FIFO-across-fall-back and a later()-across-fall-back test. Tests in tests/DatabaseQueueTimezoneTest.php and ModuleTest.
