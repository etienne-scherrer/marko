# Task 001: Reject negative TTLs in Cacheable and PageCacheConfig

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
A negative TTL is always a mistake. Today it silently behaves like `0`. Reject it loudly with a `PageCacheException` when the `#[Cacheable]` attribute is constructed and when `PageCacheConfig::defaultTtl()` reads `page-cache.default_ttl`.

## Context
- Related files: `packages/page-cache/src/Attributes/Cacheable.php`, `packages/page-cache/src/Config/PageCacheConfig.php`, `packages/page-cache/src/Exceptions/PageCacheException.php`, tests under `packages/page-cache/tests/Unit/`
- Patterns to follow: existing static factories on `PageCacheException` (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it accepts a ttl of zero`
- [x] `it rejects a negative ttl with a PageCacheException`
- [x] `it returns a default ttl of zero`
- [x] `it throws a PageCacheException when the default ttl is negative`
- [x] `it creates negativeTtl exception with message, context and suggestion`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards: `@throws ConfigNotFoundException|PageCacheException` on `PageCacheConfig::defaultTtl()`, and `@throws PageCacheException` on `CacheabilityChecker::getRouteAttribute()` (it calls `newInstance()` and catches only `ReflectionException`). `PageCacheMiddleware::handle()` already declares it.
- Scope is `packages/page-cache` ONLY. Do NOT touch `packages/page-cache-file` — task 002 (running in parallel) owns the `@throws` update on `FilePageCacheDriver::store()`.
- Add tests to the existing `tests/Unit/Attributes/CacheableTest.php`, `tests/Unit/Config/PageCacheConfigTest.php` and `tests/Unit/Exceptions/PageCacheExceptionTest.php`.
- Note: `RouteDiscovery` instantiates every method attribute at boot and only catches `Error`, so a negative `#[Cacheable]` ttl fails at route discovery (boot), not on first request. No change to routing is needed.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
