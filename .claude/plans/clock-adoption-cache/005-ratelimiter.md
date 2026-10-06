# Task 005: Adopt clock in ratelimiter

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Inject `ClockInterface` into `RateLimiter` and compute Retry-After from the clock.

## Context
- Related files: packages/ratelimiter/src/RateLimiter.php, packages/ratelimiter/tests/Unit/RateLimiterTest.php, RateLimitMiddlewareTest.php, Integration/RedisRateLimiterTest.php, packages/ratelimiter/composer.json
- Tasks 001 and 003 already add a clock to the cache-driver construction sites in these tests, so the suite is green when this task starts. This task rewires them so that **one FakeClock instance is passed to both the cache driver and the `RateLimiter`**. Retry-After is `expiresAt` (from the cache's clock) minus `now` (from the limiter's clock), so two separate clocks give results you can't predict.
- The anonymous `ArrayCacheDriver` subclass in RateLimiterTest.php (~line 210) must forward that shared clock to `parent::__construct`.
- Clock-advancing tests (count-down, window reset) must use `ArrayCacheDriver`. Redis expires keys on the server, so a FakeClock can't reset a Redis counter. In `RedisRateLimiterTest`, share one clock between the RedisCacheDriver and the limiter. Asserting an exact `retryAfter` equal to the decay window is fine; travelling the clock and expecting a reset is not.
- The "requires marko/clock" test: ratelimiter has no PackageStructureTest.php, so create `packages/ratelimiter/tests/PackageStructureTest.php`.

## Requirements (Test Descriptions)
- [x] `it computes retry after from the clock`
- [x] `it counts retry after down as the clock advances`
- [x] `it allows attempts again once the clock passes the decay window`
- [x] `requires marko/clock`

## Acceptance Criteria
- All requirements have passing tests
- No time() left in src

## Implementation Notes
One FakeClock shared by ArrayCacheDriver and RateLimiter in unit tests; the Redis integration test shares one SystemClock. The 'requires marko/clock' test lives in tests/Unit/ContractsTest.php ('package dependencies') instead of a new PackageStructureTest.php. Extra test: retryAfter is 0 on the last second of the window.
