# Task 001: Adopt clock in cache-array

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `ArrayCacheDriver` and use it for expiry, created_at and the reported item expiry.

## Context
- Related files: packages/cache-array/src/Driver/ArrayCacheDriver.php, packages/cache-array/tests/Unit/ArrayCacheDriverTest.php, packages/cache-array/composer.json, packages/sse/tests/SseConnectionLimiterTest.php (constructs the driver)
- Also constructs the driver (this task owns them so the suite stays green before task 005):
  - packages/ratelimiter/tests/Unit/RateLimiterTest.php:27 (`createRateLimitTestCache()`)
  - packages/ratelimiter/tests/Unit/RateLimiterTest.php:~210-218: anonymous class `extends ArrayCacheDriver` calling `parent::__construct(createRateLimitCacheConfig())`. Without the clock this throws an ArgumentCountError. Add a clock to its constructor and forward it.
  - packages/ratelimiter/tests/Unit/RateLimitMiddlewareTest.php:92 (`createRealRateLimiter()`)
  - Pass a `new FakeClock()` at each site, which is the minimal change. Task 005 later rewires these so the limiter and the cache share one clock.
- Patterns to follow: packages/session/src/Middleware/SessionMiddleware.php (#200)

## Requirements (Test Descriptions)
- [x] `it keeps an entry until its ttl has elapsed on the clock`
- [x] `it expires an entry one second after its ttl on the clock`
- [x] `it reports the item expiry relative to the clock`
- [x] `it restarts an expired counter relative to the clock on increment`
- [x] `requires marko/clock` (package structure)

## Acceptance Criteria
- All requirements have passing tests
- No time()/new DateTimeImmutable() left in src
- cache-array, sse and ratelimiter test suites all pass. No `ArrayCacheDriver` construction site anywhere in the repo is missing the clock argument.

## Implementation Notes
Driver takes a required `ClockInterface $clock`; set/increment read the clock once so expires_at and created_at agree; getItem builds expiresAt from `clock->now()->setTimestamp()`. Updated the sse and ratelimiter construction sites. Extra tests: default-ttl expiry, zero-ttl never expires, increment keeps the window.
