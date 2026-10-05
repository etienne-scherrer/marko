# Task 005: Middleware per-route limits, buckets and 429 response

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Remove scalar constructor params; resolve limits from `#[RateLimit]` on `$request->action()` (wins) or `$request->controller()`, falling back to `RateLimiterConfig`. Memoize per `controller::action`. Bucket key `{name ?? controller::action}|{identity}`. 429 via `Response::json()` with `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining: 0`.

## Context
- Related files: packages/ratelimiter/src/Middleware/RateLimitMiddleware.php, tests/Unit/RateLimitMiddlewareTest.php

## Requirements (Test Descriptions)
- [x] `it applies config defaults when no attribute is present`
- [x] `it applies the class attribute limits`
- [x] `it lets the method attribute override the class attribute`
- [x] `it keeps separate counters for two routes from the same ip`
- [x] `it shares a bucket across routes with the same attribute name`
- [x] `it limits an IPv6 client end to end`
- [x] `it returns a json 429 with rate limit headers`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
