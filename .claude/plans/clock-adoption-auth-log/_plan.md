# Plan: Clock Adoption — authentication, admin-auth, errors, log

## Created
2026-10-05

## Status
completed

## Objective
Route every wall-clock read in `marko/authentication`, `marko/admin-auth`, `marko/errors*` and `marko/log*` (plus the `marko/testing` `TestClient` fallback) through the PSR-20 `ClockInterface`, so time-dependent behaviour can be frozen with `FakeClock` instead of sleeping.

## Related Issues
Refs #221 (the auth + admin-auth + errors + log* group, plus the testing `TestClient` fallback)

## Discovery Notes
- `marko/clock` binds `Psr\Clock\ClockInterface` to `SystemClock` (singleton). `FakeClock` lives in `marko/testing`. The pattern from #200 is a required constructor parameter.
- authentication: `RememberTokenManager::isExpired()` uses `new DateTimeImmutable()`; `RequestCookieJar::set()/delete()` use `time()`. `RememberTokenManager` is built by a closure in `module.php` (copied into the `testing` and `roadrunner` fixture apps' vendor dirs, which must stay in sync).
- admin-auth: seven event classes default `$timestamp = new DateTimeImmutable()`. They are dispatched by `RoleRepository`/`AdminUserRepository`, which extend `Marko\Database\Repository\Repository`. That base class already has a `protected now()` time seam that the parallel database group (#221) converts to `ClockInterface`. Adding a separate constructor to the admin-auth repositories would collide with that change, so the repositories pass `$this->now()` to the events and the events' timestamp becomes a required parameter.
- errors: `ErrorReport::fromThrowable()` is a static factory on a readonly value object. Per the issue's rule for value objects, the timestamp becomes a required argument and the handlers in `errors-simple`/`errors-advanced` (which build the report) inject `ClockInterface`.
- log: `ClearCommand` cutoff uses `time()`; `FileLogger` stamps records with `new DateTimeImmutable()`; `DailyRotation` takes an optional fixed `?DateTimeImmutable $now`. `FileLoggerFactory` wires them.
- testing: `TestClient::now()` falls back to `time()` when no clock is bound; `REQUEST_TIME` uses `time()` directly.

## Scope

### In Scope
- authentication: `RememberTokenManager`, `RequestCookieJar`, `module.php` (+ fixture copies). `marko/clock` is already required.
- admin-auth: events + repositories
- errors / errors-simple / errors-advanced: `ErrorReport::fromThrowable()` timestamp, handler clock injection, module wiring, composer require
- log / log-file: `ClearCommand`, `FileLogger`, `DailyRotation`, `FileLoggerFactory`, composer require
- testing: `TestClient` falls back to `SystemClock`, `REQUEST_TIME` from the same clock
- Docs pages for each touched package and the adopter list in `clock.md`

### Out of Scope
- database, queue, scheduler, cache, ratelimiter, page-cache, notification, broadcasting, media, sse (other groups)
- Monotonic timing (`microtime(true)` durations)
- Changing `Repository::now()` itself (database group)

## Success Criteria
- [ ] No `time()` / `new DateTimeImmutable()` wall-clock reads remain in the group's `src/`
- [ ] Each converted package has a `FakeClock` test of its time-dependent behaviour
- [ ] Each package that reads the clock requires `marko/clock`
- [ ] Docs pages updated; `clock.md` lists the adopters
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | authentication: clock for remember-token expiry and cookie expiry | - | completed |
| 002 | admin-auth: events take the repository's timestamp | - | completed |
| 003 | errors: report timestamp from the handler's clock | - | completed |
| 004 | log / log-file: clock for cutoff, record time and rotation | - | completed |
| 005 | testing: TestClient falls back to SystemClock | - | completed |
| 006 | docs: package pages and clock.md adopter list | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Required `ClockInterface` constructor parameters, autowired from `marko/clock`'s binding.
- Value objects (events, `ErrorReport`) never read the clock; the code that creates them passes the instant.

## Risks & Mitigations
- Overlap with the database group on `Repository`: avoided by using the existing `now()` seam instead of a new constructor.
- Fixture apps carry copies of `authentication/module.php`: update the copies alongside the real file.
- Required clock parameters break many existing test constructions (about 60 each in authentication and errors*). Each task lists the files to update and pins exact signatures, so positional calls fail loudly instead of shifting meaning.
- FakeClock tests need `marko/testing` in `require-dev` for errors-simple, errors-advanced and log-file, which currently have only pest.
- Admin-auth event timestamps switch from PHP's default timezone to UTC, because `Repository::now()` returns UTC.
