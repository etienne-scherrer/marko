# Devil's Advocate Review: page-cache-null-expiry

## Critical (Must fix before building)
None.

## Important (Should fix before building)

1. **Tasks 001/002: nobody owns the `@throws` on `FilePageCacheDriver::store()`, so parallel workers could collide.** Once 001 lands, `store()` (line 77) calls `PageCacheConfig::defaultTtl()`, which can now throw `PageCacheException`. Task 001's acceptance criteria say "`@throws` on callers that propagate". That would send the 001 worker into `packages/page-cache-file/src/Driver/FilePageCacheDriver.php`, which is the file task 002 edits at the same time. Fix: task 002 owns the `store()` docblock and changes it to `@throws ConfigNotFoundException|PageCacheException|RandomException`. `PageCacheException` already exists, so 002 does not need to depend on 001. Task 001's scope is limited to `packages/page-cache` (`defaultTtl()` and `CacheabilityChecker::getRouteAttribute()`, which currently has no `@throws`). `PageCacheMiddleware::handle()` already declares `PageCacheException`.

2. **Plan's Architecture Notes: wrong failure point for `#[Cacheable(ttl: -1)]`.** The plan says the exception "surfaces loudly on the first request to the route". In fact `RouteDiscovery::discoverInClass()` (packages/routing/src/RouteDiscovery.php:61-64) calls `newInstance()` on every method attribute at boot. It catches only `Error`, and `PageCacheException` is an `Exception`, so a negative attribute TTL fails at route discovery, before any request. `RouteCollector::hasRouteAttributes()` also catches only `Error`. Louder is fine, but the docs in task 003 and the plan must not describe it as a runtime/first-request failure. A negative `default_ttl` is different: it is only read inside `store()`, so it surfaces on the first cache miss of a cacheable route. Fix: corrected the note in `_plan.md` and spelled out both timings in task 003.

## Minor (Nice to address)
- The shipped config uses `(int) ($_ENV['PAGE_CACHE_TTL'] ?? 3600)`. A non-numeric value such as `PAGE_CACHE_TTL=abc` casts to `0` and silently means "never expires". This is out of scope, but worth a sentence in the docs.
- Task 002 could also check that `store()` with an effective TTL of 0 writes `expires_at => null`. Existing behaviour already covers this, and the "serves" test exercises it end to end.
- `PageCacheConfigTest` / `CacheableTest` already exist. Workers should add to these files, not create new ones.

## Questions for the Team
- Should a non-numeric `PAGE_CACHE_TTL` also be rejected? Today it casts to `0`, which means "never expire". That is out of scope for #275.
