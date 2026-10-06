# Task 003: Adopt clock in cache-redis

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `RedisCacheDriver` and compute the reported `expiresAt()` from the clock plus the Redis TTL.

## Context
- Related files: packages/cache-redis/src/Driver/RedisCacheDriver.php, packages/cache-redis/tests/Unit/RedisCacheDriverTest.php, packages/cache-redis/tests/Integration/RedisCacheDriverIntegrationTest.php, packages/ratelimiter/tests/Integration/RedisRateLimiterTest.php, packages/cache-redis/composer.json
- Redis enforces expiry server-side; the clock only affects the reported expiry.
- This task owns the `RedisCacheDriver` construction site in packages/ratelimiter/tests/Integration/RedisRateLimiterTest.php. Pass a FakeClock there so the ratelimiter suite stays green before task 005, which then shares that clock with the limiter.
- The "requires marko/clock" test: cache-redis has no PackageStructureTest.php, so add it to packages/cache-redis/tests/ModuleTest.php.

## Requirements (Test Descriptions)
- [x] `it reports the item expiry as the clock time plus the remaining redis ttl`
- [x] `requires marko/clock`

## Acceptance Criteria
- All requirements have passing tests
- No time()/new DateTimeImmutable() left in src

## Implementation Notes
expiresAt = clock now + Redis TTL (via setTimestamp, no modify() exception path). Integration tests use SystemClock (real server). Verified against a throwaway redis:7.4 container: integration suites pass.
