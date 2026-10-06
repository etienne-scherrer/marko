# Devil's Advocate Review: clock-adoption-cache

## Critical (Must fix before building)

### C1. Task 001 breaks the ratelimiter test suite, and nobody owns those call sites until task 005 (001, 005)
`ArrayCacheDriver` is built by hand in three ratelimiter test spots that task 001 does not list:
- `packages/ratelimiter/tests/Unit/RateLimiterTest.php:27` `createRateLimitTestCache()` -> `new ArrayCacheDriver(createRateLimitCacheConfig())`
- `packages/ratelimiter/tests/Unit/RateLimiterTest.php:210-218`: an anonymous class `extends ArrayCacheDriver` that calls `parent::__construct(createRateLimitCacheConfig())`. Once the clock parameter is required this throws an `ArgumentCountError`.
- `packages/ratelimiter/tests/Unit/RateLimitMiddlewareTest.php:92` `createRealRateLimiter()`

When 001 lands, `composer test` goes red in a package that 001's worker was never told to touch. That stays true until 005 runs, and 005 can't start until 001 and 003 are done. The worker either stalls on failures it doesn't own or edits out of scope.
**Fix:** Task 001 owns every `ArrayCacheDriver` construction site, including these three. It passes a `FakeClock` so the suite stays green. Task 005 then rewires those sites so the limiter and the cache share one clock. Task 003 already lists `RedisRateLimiterTest.php` for the matching `RedisCacheDriver` site.

## Important (Should fix before building)

### I1. Raw fixtures and assertions that use the real `time()` will flip once the driver reads a frozen FakeClock (004, 002)
Tests pin FakeClock to a fixed instant, as the existing pattern does (`'2026-01-01 12:00:00 UTC'`). Any test that writes a serialized payload or asserts against the real `time()` then compares two different timelines:
- `packages/page-cache-file/tests/helpers.php:74-75` `writeExpiredPageCacheEntry()` writes `expires_at => time() - 10`. Relative to a clock pinned in the past, that time is in the future, so the "expired" entry gets served.
- `FilePageCacheDriverTest.php:76` (`toBeGreaterThan(time())`), `:103-104` and `:119-120` (window around `time() + ttl`), and `:256-257` and `:286-287` (`time() + 9999` payloads).
- `createPageCacheFileDriver()` and `createPageCacheFileDriverWithPaths()` in `helpers.php` need a clock argument.
The cache-file equivalent (`writeExpiredCacheEntry()`) has already been moved to take `$now`. That should be written into the plan so it isn't undone.
**Fix:** Spell this out in task 004 and add a note to task 002. Fixtures and assertions derive from `$clock->now()->getTimestamp()`. The helpers take an optional `?ClockInterface $clock = null` that defaults to `new FakeClock()`, so existing callers keep compiling.

### I2. Task 005's Retry-After tests only work if the limiter and the cache share one FakeClock, and Redis can't be time-travelled (005)
`RateLimiter::attempt()` computes `expiresAt (from the cache driver's clock) - now (from the limiter's clock)`. If a test gives each a separate `new FakeClock()`, the result depends on two clocks that disagree. The requirement `it allows attempts again once the clock passes the decay window` only works with `ArrayCacheDriver`. Redis expires keys on the server, so a FakeClock can't make a Redis counter expire.
**Fix:** Task 005 requires one `FakeClock` instance passed to both the `ArrayCacheDriver` and the `RateLimiter`. Clock-advancing tests must use the array driver. `RedisRateLimiterTest` shares one clock and can assert an exact `retryAfter` equal to the decay window, but must not travel the clock to expect a reset. The anonymous `ArrayCacheDriver` subclass at `RateLimiterTest.php:210` must forward the clock to `parent::__construct`.

## Minor (Nice to address)

- **003/005 "requires marko/clock" test location:** cache-redis and ratelimiter have no `PackageStructureTest.php`. Put the assertion in `packages/cache-redis/tests/ModuleTest.php`, and in a new `packages/ratelimiter/tests/PackageStructureTest.php` or an existing ratelimiter test file.
- **003 unit test:** use a mocked client whose `ttl()` returns a fixed value, for example 90, and assert `expiresAt()->getTimestamp() === clock + 90`. Keep coverage for `ttl()` returning -1 or -2 (reports `null`).
- **Success-criterion grep:** `date(` also matches `update(` and similar. Use `\bdate\(` or `[^a-z_]date\(`.
- **Time precision:** `FakeClock()` defaults to `'now'` with microseconds. `getTimestamp()` truncates them, so exact assertions are safe. Pinning a fixed string, as the other tests do, is still clearer.

## Questions for the Team

- **Pre-existing page-cache-file bug, out of scope:** `FilePageCacheDriver::lookup()` uses `isset(..., $data['expires_at'])`, which is false for `null`. A page stored with no expiry (policy ttl 0 and `default_ttl` 0) is therefore never served, and the `$data['expires_at'] !== null` branch is dead code. Fix it in this PR, since the expiry check is being rewritten anyway, or file a separate issue?
- **Constructor change:** plan keeps the clock as a required constructor parameter (matches #200). Is an explicit "BC note" in the PR description enough for anyone who subclasses `ArrayCacheDriver` or `RateLimiter` with their own constructor?
