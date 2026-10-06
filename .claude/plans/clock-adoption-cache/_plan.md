# Plan: Clock Adoption (cache, ratelimiter, page-cache)

## Created
2026-10-05

## Status
completed

## Objective
Make the cache drivers, the rate limiter and the file page-cache driver read time through an injected PSR-20 `ClockInterface` instead of `time()` / `new DateTimeImmutable()`, so expiry and Retry-After can be tested with `FakeClock` without sleeping.

## Related Issues
Relates to #221 (cache-* + ratelimiter + page-cache group; the other groups land in separate PRs)

## Discovery Notes
- `marko/clock` binds `Psr\Clock\ClockInterface` to `SystemClock` (singleton); `FakeClock` lives in `marko/testing`. #200 adopted it in session with a required constructor parameter and a `marko/clock` require — same pattern here.
- Wall-clock reads in the group (from the issue's grep): `ArrayCacheDriver` (expiry, created_at, getItem expiresAt), `FileCacheDriver` (same plus increment), `RedisCacheDriver::getItem()` (reported expiry = now + TTL), `RateLimiter::attempt()` (Retry-After), `FilePageCacheDriver` (lookup expiry, store expiry/created_at).
- `marko/cache` and `marko/page-cache` (interface packages) have no wall-clock reads; nothing to change there.
- Hand-construction sites are only tests. Docs never show hand construction, so the drivers are container-resolved in practice. Cross-package sites:
  - `packages/sse/tests/SseConnectionLimiterTest.php` builds an `ArrayCacheDriver`.
  - The ratelimiter tests build `ArrayCacheDriver` (RateLimiterTest including an anonymous subclass, and RateLimitMiddlewareTest) and `RedisCacheDriver` (RedisRateLimiterTest).
  - The driver task that changes a constructor (001/003) updates every site with a FakeClock so the suite stays green. Task 005 then makes the limiter and the cache share one clock.
- Test fixtures that write raw payloads or assert against the real `time()` (page-cache-file helpers/tests, cache-file `writeExpiredCacheEntry`) must derive from the test clock. A FakeClock frozen at a fixed instant otherwise puts them on a different timeline.
- Redis expiry itself is server-side (`EXPIRE`); only the reported `expiresAt()` moves with the clock. Document that a FakeClock does not expire Redis keys.

## Scope

### In Scope
- Inject `ClockInterface $clock` into ArrayCacheDriver, FileCacheDriver, RedisCacheDriver, RateLimiter, FilePageCacheDriver
- Add `marko/clock` to `require` of cache-array, cache-file, cache-redis, ratelimiter, page-cache-file
- FakeClock tests for expiry / created_at / Retry-After without sleeping
- Update all hand-construction call sites in tests (incl. the sse test)
- Docs pages for the five packages + clock.md adopting-packages list

### Out of Scope
- Other #221 groups (database/queue/scheduler, auth/admin-auth/errors/log, notification/broadcasting/media/sse source code)
- Monotonic timing (health, debugbar, devserver)

## Success Criteria
- [x] grep for `time()`, `new DateTimeImmutable()`, `microtime(`, `date(` finds nothing in the group's src
- [x] Each converted package has a FakeClock test for its time-dependent behaviour
- [x] Each touched package requires marko/clock; docs pages mention ClockInterface and show FakeClock where time is user-visible; clock.md lists them
- [x] All tests passing; `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Adopt clock in cache-array | - | completed |
| 002 | Adopt clock in cache-file | - | completed |
| 003 | Adopt clock in cache-redis | - | completed |
| 004 | Adopt clock in page-cache-file | - | completed |
| 005 | Adopt clock in ratelimiter | 001, 003 | completed |
| 006 | Docs for adopting packages | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Required constructor parameter `ClockInterface $clock` (naming rule: interface minus suffix), appended after existing params.
- Use `$this->clock->now()->getTimestamp()` for Unix timestamps; build `expiresAt` via `$this->clock->now()->setTimestamp(...)` so the clock's timezone carries through.
- Read the clock once per write so expiry and created_at agree.
- RateLimiter's Retry-After subtracts its own clock from the cache's reported expiry, so tests must inject the same FakeClock into both. Redis expiry is server-side, so clock-advancing ratelimiter tests use ArrayCacheDriver.

## Risks & Mitigations
- Public constructor change: drivers are container-resolved; call out in the PR for anyone constructing by hand.
- Conflicts on clock.md with the other group PRs: keep the edit to appended list items.
