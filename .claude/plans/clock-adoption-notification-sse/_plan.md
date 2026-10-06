# Plan: Clock Adoption (notification, broadcasting, media, sse)

## Created
2026-10-05

## Status
completed

## Objective
Read wall-clock time through an injected PSR-20 `ClockInterface` in the notification, notification-database, broadcasting-amphp, broadcasting-mercure, broadcasting-pusher, media and sse packages, so time-dependent behaviour can be tested with `FakeClock` without sleeping.

## Related Issues
Relates to #221 (this is the notification*/broadcasting*/media/sse group; the other three groups ship in separate PRs)

## Discovery Notes
- `marko/clock` binds `Psr\Clock\ClockInterface` to `SystemClock` (singleton). `FakeClock` lives in `marko/testing`. The #200 pattern (session) is a required constructor parameter plus `marko/clock` in `require`.
- Wall-clock reads in this group (grep on develop):
  - notification `DatabaseChannel` (`date('Y-m-d H:i:s')` created_at, single + batch)
  - notification-database `DatabaseNotificationRepository::markAsRead/markAllAsRead` (read_at)
  - broadcasting-amphp `AmphpSignature::verify` (expiry), `AmphpSubscriberToken::for` (expiry), `AmphpBroadcaster::generateId` (time-ordered id, `microtime`), `ReplayBuffer` (`Closure` clock defaulting to `microtime(true)`), constructed by `AmphpSseServer::start()`
  - broadcasting-mercure `MercureSubscriberToken::for` (JWT `exp`) and `withAuthorizationCookie` (cookie expiry)
  - broadcasting-pusher `PusherBroadcaster::sendRequest` (`auth_timestamp`)
  - media `MediaManager::upload` (`date('Y/m')` path prefix)
  - sse `SseStream` heartbeat and timeout loops
- All classes except `SseStream` are container-built (autowired). `SseStream` is a value object users construct by hand in controllers, so the clock is an optional trailing parameter there.
- notification-database has no `marko/testing` in `require-dev`. Task 001 adds it.
- broadcasting-amphp tests subclass `AmphpSseServer` (AmphpSseServerTest) and construct `ReplayBuffer` directly (ChannelHubTest). Task 002 lists every construction site that has to change.
- broadcasting-pusher is concurrently being changed by #228; edits there are limited to the constructor and the one `time()` call.

## Scope

### In Scope
- Inject `ClockInterface` in each class above; add `marko/clock` to each package's `require`
- Replace `ReplayBuffer`'s `Closure` clock with `ClockInterface` outright (it is internal to the server, not documented public API)
- FakeClock-based tests for each package's time-dependent behaviour
- Docs pages for each touched package mention `ClockInterface`; `clock.md` lists the adopters

### Out of Scope
- Other #221 groups (database/queue/scheduler, cache/ratelimiter/page-cache, auth/admin-auth/errors/log + testing)
- `uniqid()` in MediaManager (an id generator, not a clock read)
- `sleep()` in SseStream's poll loop (pacing, not a time read)

## Success Criteria
- [x] The #221 grep over these packages' `src/` finds no `time()`, `date(`, `microtime(` or `new DateTimeImmutable()`
- [x] Every converted package has a FakeClock test covering its time-dependent behaviour, with no sleeping
- [x] `ReplayBuffer` no longer accepts a `Closure` clock
- [x] Each touched package requires `marko/clock`; docs updated; `clock.md` lists the adopters
- [x] All tests passing (`composer ci` green)
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | notification + notification-database timestamps via clock | - | completed |
| 002 | broadcasting-amphp signature, token, ids and ReplayBuffer via clock | - | completed |
| 003 | broadcasting-mercure and broadcasting-pusher via clock | - | completed |
| 004 | media upload path via clock | - | completed |
| 005 | sse heartbeat and timeout via clock | - | completed |
| 006 | docs pages, READMEs and clock.md adopter list | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Required trailing `private ClockInterface $clock` constructor parameter for container-built classes (matches session/#200).
- `SseStream`: `private ClockInterface $clock = new SystemClock()` — hand-constructed value object; the default keeps existing `new SseStream(...)` calls working while making the dependency visible and overridable.
- Formats: `$clock->now()->format('Y-m-d H:i:s')` (same default timezone semantics as `date()`), `getTimestamp()` for unix seconds, `(float) format('U.u')` for ReplayBuffer, `format('Uv')` for millisecond ids.

## Risks & Mitigations
- Public constructors change: call out in the PR body; container-built classes are rarely constructed by hand.
- Conflicts with #228 (pusher) and the other #221 groups (clock.md list, composer.json): keep edits minimal; trivial rebases.
