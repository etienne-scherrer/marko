# Task 003: RateLimiter hashed cache keys

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`RateLimiter::getCacheKey()` returns `'rate_limit.' . hash('xxh128', $key)` so any caller-supplied key (IPv6, email, route name) is cache-safe.

## Context
- Related files: packages/ratelimiter/src/RateLimiter.php, packages/ratelimiter/tests/Unit/RateLimiterTest.php (update tests that read `rate_limit.test-key` directly)

## Requirements (Test Descriptions)
- [x] `it limits an IPv6 key without throwing InvalidKeyException`
- [x] `it hashes the caller key into a cache-safe key`
- [x] `it reports tooManyAttempts and clears for an IPv6 key`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
