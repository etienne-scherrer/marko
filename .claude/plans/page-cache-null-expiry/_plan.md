# Plan: Page Cache Null Expiry

## Created
2026-10-05

## Status
completed

## Objective
Make a page stored with an effective TTL of `0` ("never expires") actually servable by the file driver, reject negative TTLs loudly, and fix the page-cache docs so the config key is `default_ttl` and the meaning of `0` is documented.

## Related Issues
Closes #275

## Discovery Notes
- `FilePageCacheDriver::store()` writes `expires_at => null` when the effective TTL (`$policy->ttl > 0 ? $policy->ttl : default_ttl`) is `<= 0`, but `lookup()` validates with `isset(..., $data['expires_at'])`, which rejects `null`, so the entry is never served. The `!== null` guard after it shows "null = never expires" was the intent.
- #270 (merged) already injects `Psr\Clock\ClockInterface` into the driver; tests use `FakeClock` via `createPageCacheFileDriver($tmpDir, $defaultTtl, $clock)`.
- `#[Cacheable(ttl: 0)]` falls back to `page-cache.default_ttl` (existing behaviour, kept). The effective TTL is `0` only when `default_ttl` is `0` (`PAGE_CACHE_TTL=0`).
- Negative TTLs currently behave like `0`. Decision (per issue recommendation): reject them with `PageCacheException` in the `Cacheable` constructor and in `PageCacheConfig::defaultTtl()`.
- Docs drift: `page-cache.md` / `page-cache-file.md` show `'ttl' => (int) env(...)` and `PageCacheConfig::ttl()`; shipped config is `default_ttl` read via `$_ENV`, getter is `defaultTtl()`.

## Scope

### In Scope
- `lookup()` accepts `expires_at === null` (never expires), rejects missing or non-`int|null` `expires_at`
- Negative TTL rejected at `#[Cacheable]` construction and `PageCacheConfig::defaultTtl()` with a helpful `PageCacheException`
- Tests with `FakeClock` for null expiry served, purged by URL/tag/clear, positive TTL still expiring, corrupt payloads
- Docs pages + README sanity: `default_ttl` key, `defaultTtl()` getter, meaning of `0`, negative TTL rejection

### Out of Scope
- Changing `#[Cacheable(ttl: 0)]` semantics (still "use `default_ttl`")
- Validation in `CachePolicy` (built from the already-validated attribute)
- Other page-cache drivers (none exist)

## Success Criteria
- [x] A page stored with effective TTL `0` is served and stays served however far a `FakeClock` advances
- [x] That entry is removed by `purgeUrl()`, `purgeTag()` and `clear()`
- [x] Positive-TTL entries still miss and are deleted once expired
- [x] Payloads with missing or non-`int|null` `expires_at` are misses
- [x] Negative TTL rejected with clear message (attribute and config)
- [x] Docs updated
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Reject negative TTLs in Cacheable and PageCacheConfig | - | completed |
| 002 | Serve never-expiring entries in FilePageCacheDriver | - | completed |
| 003 | Update page-cache docs pages | 001, 002 | completed |

## Architecture Notes
- Exception via static factory `PageCacheException::negativeTtl(int $ttl, string $source)` with message/context/suggestion.
- `Cacheable` is a `readonly class`; validation goes in the constructor body alongside promoted properties.
- `#[Cacheable]` is instantiated at boot by `RouteDiscovery::discoverInClass()` (iterates all method attributes, catches only `Error`), so a negative attribute ttl fails loudly at route discovery. `CacheabilityChecker::getRouteAttribute()` (catches only `ReflectionException`) also instantiates it per request; it gets a `@throws PageCacheException` docblock.
- A negative `default_ttl` is only read in `FilePageCacheDriver::store()`, so it surfaces on the first cache miss of a cacheable route.
- File ownership for parallel work: task 001 touches only `packages/page-cache`; task 002 owns all edits to `packages/page-cache-file` (including adding `PageCacheException` to `store()`'s `@throws`).

## Risks & Mitigations
- Existing tests relying on negative TTL: none found (grep).
- Never-expiring entries could live forever: documented that they are purged by tag, URL or `page-cache:clear`.
