# Task 004: Application cached boot + CLI bypass

**Status**: pending
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Decide the cache gate before module discovery. On a cached boot build modules from the cache (ManifestParser::parseCached), register PSR-4 autoloaders from them, use the cached global middleware and bind a `CachedDiscovery` with the contributor sections. Make ModuleDiscovery, ManifestParser and ClassFileParser constructor-injectable (test doubles), bind ClassFileParser and PreferenceRegistry in the container, and add `initialize(bool $useDiscoveryCache = true)`. CliKernel boots `discovery:cache`/`discovery:clear` with the cache bypassed.

## Context
- This task owns ALL changes to `Application.php` (tasks 005/006 must not edit it).
- `ModuleAutoloader::register()` runs its own ModuleDiscovery + `ManifestParser::parse`. Add `ModuleAutoloader::registerModules(array $modules)` (same idempotent PSR-4 logic, no discovery); `register()` delegates to it. Cached boot calls `registerModules()` with cached non-vendor modules; live boot and `marko/testing` TestCase stay as today.
- `discoverRoutes()`: pass the injected ClassFileParser (not `new ClassFileParser()`); on a cached boot use the cached global middleware instead of `GlobalMiddlewareResolver`.
- Bind `ClassFileParser` and `PreferenceRegistry` instances before observers/commands/routes run.
- `CachedDiscovery` contract (consumed by 005/006 in parallel): `Marko\Core\Discovery\CachedDiscovery`, `__construct(?array $sections = null)` — a no-arg instance is uncached so it autowires in tests that never boot Application; `isCached(): bool`; `section(string $key): ?array` returns null when uncached, the data when cached, throws `DiscoveryCacheException::missingSection($key)` when cached but absent. Bound as an instance on every boot.
- Stale on module.php drift: after `parseCached()`, throw `stale` if the live module.php disables the module or its `after`/`before`/`globalMiddleware` differ from the CachedModule snapshot.
- CliKernel: parse `Input` before `initialize()`; call `initialize(false)` positionally (factory fakes in CliKernelTest may not declare the parameter).
- Update existing `ApplicationDiscoveryCacheTest` helpers to write v3 payloads.

## Requirements (Test Descriptions)
- [ ] `it boots from a warm cache without calling ModuleDiscovery, ManifestParser::parse or ClassFileParser`
- [ ] `it boots the same modules in the same order from the cache as from live discovery`
- [ ] `it registers PSR-4 autoloaders for app and modules modules from the cache`
- [ ] `it binds CachedDiscovery with the cached sections, and an uncached one on a live boot`
- [ ] `it throws stale when a cached module's module.php now disables it`
- [ ] `it throws stale when a cached module's sequence or global middleware changed in module.php`
- [ ] `it uses the cached global middleware order on a cached boot`
- [ ] `it throws missingSection when a cached boot asks for an absent section`
- [ ] `it autowires an uncached CachedDiscovery when constructed with no arguments`
- [ ] `it ignores the cache when initialize is called with useDiscoveryCache false`
- [ ] `it runs discovery:cache and discovery:clear with the cache bypassed in the CLI`

## Acceptance Criteria
- All requirements have passing tests; existing ApplicationDiscoveryCache and CliKernel tests pass (updated to the v3 payload)

## Implementation Notes
(Left blank - filled in by programmer during implementation)
