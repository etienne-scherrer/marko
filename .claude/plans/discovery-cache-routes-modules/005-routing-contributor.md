# Task 005: Routing contributor and hydration

**Status**: pending
**Depends on**: 004
**Retry count**: 0

## Description
Extract live route discovery from RoutingBootstrapper into `RouteCollector`. Add `RouteCacheContributor` (key `routes`) that serializes the collected routes as constructor arguments in registration order, declare it in routing's module.php, and make `RoutingBootstrapper::boot()` hydrate from `CachedDiscovery` when present.

## Context
- Do NOT edit `Application.php` (owned by 004). `RoutingBootstrapper::boot()` reads `CachedDiscovery` from the container (`$container->has()` guard) and hydrates when `isCached()`; otherwise runs `RouteCollector`.
- `RouteCacheContributor` gets `PreferenceRegistry` and `ClassFileParser` via constructor injection (bound by 004).
- The "not loaded" test must generate uniquely named controller classes per test and assert `class_exists($class, false) === false`, otherwise classes loaded earlier in the process mask the result.

## Requirements (Test Descriptions)
- [ ] `it hydrates routes identical to live discovery in order, names, middleware, constraints and exclusions`
- [ ] `it caches routes inherited through a Preference`
- [ ] `it does not load controllers that the request did not match`
- [ ] `it throws malformed when a cached route record is invalid`
- [ ] `it still rejects an excluded middleware missing from the stack on a cached boot`
- [ ] `it declares the route contributor in module.php`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
