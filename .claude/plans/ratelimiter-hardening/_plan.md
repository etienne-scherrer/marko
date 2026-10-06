# Plan: Rate Limiter Hardening

## Created
2026-10-05

## Status
completed

## Objective
Make `marko/ratelimiter` safe with `marko/cache-redis` and IPv6 clients, and let routes declare their own limits and buckets via a `#[RateLimit]` attribute backed by config defaults.

## Related Issues
Closes #165

## Discovery Notes
- `RedisCacheDriver::increment()` stores a raw integer via `INCR`, but `get()`/`getItem()`/`getMultiple()` always run `CacheValueSigner::verifyAndUnwrap()`, so reading a counter throws `TamperedCacheValueException`. `INCR` + `EXPIRE` are two round trips (crash window → key never expires).
- `ArrayCacheDriver` and `FileCacheDriver` already store counters as PHP ints, so `get()` returns `int` — only Redis is broken.
- `RateLimiter::getCacheKey()` concatenates the raw key; IPv6 `:` is rejected by `InvalidKeyException::isValidKey()`.
- `RateLimitMiddleware` takes scalar `$maxAttempts`/`$decaySeconds` constructor params the container cannot set; bucket key is just the client IP.
- `Router::handle()` calls `$request->withRoute(controller, action)` before the middleware pipeline, so the middleware can reflect on the target.
- No Redis server in CI today. Redis integration tests skip with a clear reason when Redis is unreachable; the CI `Tests` job gains a `redis` service so they actually run there.
- Hotspot: #166 edits `packages/cache-redis/` connection/config. This plan touches only `src/Driver/RedisCacheDriver.php`, its tests, and the docs page.

## Scope

### In Scope
- Redis driver: integer-aware `decode()` for reads; atomic `increment()` via Lua `EVAL` (also self-heals a counter that lost its TTL)
- `CacheInterface::increment()` docblock: `get()` on an incremented key returns `int`
- Array/file driver tests proving `get()` returns `int` after `increment()`
- `RateLimiter` hashes keys (`rate_limit.` + xxh128)
- `#[RateLimit]` attribute, `RateLimiterConfig`, config defaults, `RateLimitKeyResolverInterface` + default `ClientIpKeyResolver`
- Middleware: attribute resolution (method > class > config), memoized reflection, per-route bucket key, `Response::json()` 429 with `X-RateLimit-*` headers
- Redis integration tests (skip when Redis unavailable) + CI redis service
- Docs page + README updates for ratelimiter and cache-redis

### Out of Scope
- Redis connection/config changes (#166)
- Sliding-window or token-bucket algorithms
- Facades/helpers

## Success Criteria
- [x] Limit 2 against real Redis: 3rd `attempt()` returns `allowed=false` with positive `retryAfter`, no throw
- [x] `RedisCacheDriver::get()`/`getItem()` on an incremented key returns `int`; tampered non-integer still throws
- [x] `increment()` sets TTL atomically (real Redis: key always has TTL after first increment)
- [x] IPv6 client `2001:db8::1` limited correctly, no `InvalidKeyException`
- [x] Separate counters per `#[RateLimit]` route; method attribute overrides class attribute
- [x] Config defaults apply when no attribute present
- [x] Docs updated
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Redis driver: integer-aware reads + atomic increment | - | completed |
| 002 | Increment-returns-int contract (interface docblock + array/file tests) | - | completed |
| 003 | RateLimiter hashed cache keys (IPv6-safe) | - | completed |
| 004 | RateLimit attribute, RateLimiterConfig, key resolver | - | completed |
| 005 | Middleware: per-route limits, buckets, 429 response | 003, 004 | completed |
| 006 | Redis integration tests + CI redis service | 001, 003 | completed |
| 007 | Docs page + README updates | 001, 002, 003, 004, 005, 006 | completed |

## Architecture Notes
- Counters are not signed: only values matching `/^-?\d+$/` bypass the HMAC check. A signed envelope always starts with 64 hex chars + `.`, so it can never match. Integers are never passed to `unserialize()`, so the object-injection risk the signer guards against does not apply.
- Lua script: `INCR`; if ttl > 0 and (value == 1 or `TTL` == -1) then `EXPIRE`. Runs atomically on every supported Redis version.
- `marko/ratelimiter` gains only a `require-dev` on `marko/cache-redis` for the integration test; no runtime dependency on a driver.
- Middleware is no longer `readonly` (memoization cache); injected deps are individually `readonly`.

## Risks & Mitigations
- Breaking change to middleware constructor: only reachable via Preference; documented in PR.
- Existing counters keyed by raw key become orphaned after hashing: they expire within one decay window.
- CI workflow is shared: change is additive (a `services:` block on the Tests job).

## Execution Notes
- The `post-plan` devils-advocate review could not run (subagent concurrency limit reached); the plan was self-reviewed instead.
- Executed sequentially in dependency order with TDD (red, green, refactor). The Redis regression tests were also confirmed to fail against the original `RedisCacheDriver`.
