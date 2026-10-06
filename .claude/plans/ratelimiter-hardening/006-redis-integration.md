# Task 006: Redis integration tests + CI redis service

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Run the rate limiter and Redis driver against a real Redis server. Tests skip with a clear reason when Redis is unreachable; the CI `Tests` job gets a redis service so they run there.

## Context
- Related files: packages/cache-redis/tests/Integration/, packages/ratelimiter/tests/Integration/, .github/workflows/ci.yml, packages/ratelimiter/composer.json (require-dev marko/cache-redis)

## Requirements (Test Descriptions)
- [x] `it denies the third attempt with a positive retryAfter against redis`
- [x] `it reads an incremented counter back as an int from redis`
- [x] `it always leaves a ttl on the key after the first increment`
- [x] `it limits an IPv6 client against redis`

## Acceptance Criteria
- Tests pass against real Redis locally and in CI

## Implementation Notes
