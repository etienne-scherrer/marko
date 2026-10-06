# Plan: Route and Module Discovery Cache

## Created
2026-10-05

## Status
ready

## Objective
Extend the existing `marko discovery:cache` so a production request with a warm cache skips module discovery, route discovery, global middleware resolution and entity discovery, and detects a stale cache loudly.

## Related Issues
Closes #173

## Discovery Notes
- `Application::initialize()` scans `vendor/*/*` (json_decode of every composer.json), requires every module.php, topologically sorts, then `RoutingBootstrapper::discoverRoutes()` tokenizes and `require_once`s every file under every module's `src/`. `marko/database`'s boot callback scans for entities on every request.
- The existing discovery cache (`DiscoveryCache`, format version 2) covers preferences, plugins, observers and commands only, and is written by `DiscoveryCompiler` via `discovery:cache`. The gate already uses `AppEnvironment` (development/local never use the cache) and mismatched versions throw `versionMismatch`.
- `RouteDefinition` (#172) is built from plain strings/lists (`method, path, controller, action, middleware, name, withoutMiddleware`), so routes can be stored as constructor arguments. `RouteCollection` (#171) sorts lazily and stably by registration order, so storing routes in registration order reproduces the same `all()`, `names()` and match order.
- The CLI (`CliKernel`) boots the application with the cache before dispatching any command, so a stale/corrupt cache would also break `discovery:clear`/`discovery:cache` — those two commands must boot with the cache bypassed.
- `PreferenceRegistry` is not bound in the container today; a route contributor resolved from the container needs the live registry.
- The integration suite has a `->todo()` for #173 in `tests/Integration/App/KnownGapsTest.php` to be flipped into a real test.

## Scope

### In Scope
- `DiscoveryCacheContributorInterface` in core; modules declare contributors under a `'discovery'` key in `module.php`.
- Cache format version 3: ordered module list (composer-derived fields, project-relative paths), global middleware order, fingerprint, contributor sections.
- Cached boot: no vendor scan, no composer.json parsing, no `DependencyResolver`, module.php still required (closures stay live), PSR-4 autoloaders registered from cached manifests.
- Loud stale detection (`DiscoveryCacheException::stale()`) from a fingerprint of `vendor/composer/installed.json` plus module directories under `modules/` and `app/`.
- Routing contributor + hydration in `RoutingBootstrapper`; controllers load lazily.
- Database entity contributor + boot callback hydration.
- `discovery:cache` / `discovery:clear` boot with the cache bypassed.
- Benchmark script under `bin/`.
- Docs: core.md, routing.md, database.md; integration test replaces the #173 todo.

### Out of Scope
- Caching `RouteDefinition` derived fields (regex etc.) — the definition is rebuilt from constructor arguments.
- Automatic cache rebuilds (never silently rebuild).
- Seeder discovery (runs lazily in a binding closure, not per request).

## Success Criteria
- [ ] Warm cache + `APP_ENV=production`: zero ModuleDiscovery/ManifestParser::parse/ClassFileParser calls during boot + dispatch
- [ ] Unmatched controllers are not loaded after dispatch
- [ ] Cached routes identical to live discovery (order, names, middleware, constraints, Preference-inherited routes)
- [ ] Entity list and global middleware order hydrate from the cache
- [ ] Changing installed.json after caching throws `DiscoveryCacheException` on boot
- [ ] Old-version cache files throw `versionMismatch`
- [ ] Benchmark: cached boot + 404 at least 3x faster on the 600-class fixture
- [ ] Docs updated (core, routing, database; "Deploying to production")
- [ ] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Contributor contract, manifest `discovery` key, cached manifest parsing | - | pending |
| 002 | Cache format v3: modules, middleware, sections, fingerprint, stale | 001 | pending |
| 003 | DiscoveryCompiler runs contributors; discovery:cache output | 001, 002 | pending |
| 004 | Application cached boot + CliKernel bypass | 001, 002, 003 | pending |
| 005 | Routing contributor + hydration | 004 | pending |
| 006 | Database entity contributor | 004 | pending |
| 007 | Benchmark script | 005 | pending |
| 008 | Integration test + docs | 005, 006, 007 | pending |

## Architecture Notes
- Core never imports routing/database classes beyond the existing `class_exists(RoutingBootstrapper::class)` guard and `GlobalMiddlewareResolver`.
- Contributors are resolved through the container (so they get injected dependencies) and must return var_export-able data (scalars, null, arrays) — anything else is a loud error at compile time.
- Runtime access to sections goes through a core `CachedDiscovery` object bound in the container: `section($key)` returns null on a live boot, the data on a cached boot, and throws when cached but the section is missing.
- Fingerprint = xxh128 over the hash of `vendor/composer/installed.json` (or a marker when absent) plus the sorted project-relative directories holding a `composer.json` under `modules/` (recursive, stopping at a module) and `app/` (one level), plus a content hash of each of those composer.json files. Computed by `DiscoveryCache` from `ProjectPaths` on write and compared on load.
- `CachedDiscovery` contract (task 004, consumed by 005/006): `__construct(?array $sections = null)`, `isCached()`, `section($key): ?array`; no-arg instance is uncached so it autowires outside Application.
- Task 004 owns every `Application.php` edit (incl. `ModuleAutoloader::registerModules()`, injected ClassFileParser into RoutingBootstrapper, cached global middleware); 005/006 never touch it — RoutingBootstrapper reads `CachedDiscovery` from the container.
- CachedModule snapshots module.php `after`/`before`/`globalMiddleware`; a cached boot throws `stale` when live module.php differs.
- Routes stored in registration order; `RouteCollection` recomputes precedence order deterministically, so match order is identical.

## Risks & Mitigations
- Stale cache after deploy breaks the CLI itself: `discovery:cache`/`discovery:clear` boot with the cache bypassed.
- module.php edits are not fingerprinted: a cached module whose module.php now disables it or changes sequence/globalMiddleware throws `stale`; enabling a previously disabled module is not detected — docs state that `discovery:cache` must run on every deploy.
- Absolute paths baked into the cache break when a build is moved: module paths are stored relative to the project root.
