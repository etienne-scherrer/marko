# Task 006: Rate limiter on redis, IPv6, per-route (#165)

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Convert the #165 todos into `tests/Integration/App/RateLimitTest.php`. Give `/limited` a small `#[RateLimit]` and add a second limited route with its own limit. Use a random client IP per test, since Redis is shared and never flushed.

## Context
- Related files: `Fixture/app/integration/src/Http/IntegrationController.php`, `packages/ratelimiter/src/Middleware/RateLimitMiddleware.php`

## Requirements (Test Descriptions)
- [x] `it returns 429 rather than 500 once the rate limit is hit on cache-redis`
- [x] `it rate limits a client that connects over IPv6`
- [x] `it keeps separate rate-limit counters for different routes`

## Acceptance Criteria
- Tests pass against real Redis

## Implementation Notes
- Randomise the IPv6 client too (e.g. `2001:db8::` + random hextets), not just IPv4. A fixed `2001:db8::1` starts already limited on a rerun inside the decay window, because Redis is never flushed.
- Pass the address through `integrationRequest(..., server: ['REMOTE_ADDR' => $ip])`.
- Use `#[RateLimit(maxAttempts: N, decaySeconds: ...)]`. Unnamed limits are keyed per controller action, so the second route gets its own counter without a `name`.
