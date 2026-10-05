# Task 003: DiscoveryEnvironment and Application wiring

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`DiscoveryEnvironment` falls back to `getenv()` for its keys and delegates `environment()` to `AppEnvironment`. `Application` registers `AppEnvironment` as a shared container instance and gates the discovery cache on `!isDevelopment()`.

## Context
- Related files: packages/core/src/Discovery/DiscoveryEnvironment.php, packages/core/src/Application.php, packages/core/tests/Unit/Discovery/DiscoveryEnvironmentTest.php, packages/core/tests/Unit/ApplicationDiscoveryCacheTest.php
- Keep the Application change to the cache gate and service registration (shared hotspot with #163, #173).
- `DiscoveryEnvironment` gets an optional constructor argument `?AppEnvironment $appEnvironment = null` (defaults to `new AppEnvironment()`). Existing `new DiscoveryEnvironment()` call sites and autowiring through `DiscoveryCache` (DiscoveryCacheCommand, DiscoveryClearCommand) keep working.
- In `Application::initialize()`, create one `AppEnvironment`, register it with `container->instance()` next to `ProjectPaths` (before module bindings), pass it to `new DiscoveryEnvironment($appEnvironment)`, and gate on `!$appEnvironment->isDevelopment()`.
- `environment()` now honours `MARKO_ENV` (via `AppEnvironment::name()`), which is intended.
- Extend `cacheTestSnapshotEnv()` in ApplicationDiscoveryCacheTest to include `MARKO_ENV` and to clear and restore the `putenv` state for all snapshotted keys, so host env vars cannot flip results.

## Requirements (Test Descriptions)
- [x] `it reads APP_ENV from getenv when $_ENV lacks it`
- [x] `it reads DISCOVERY_CACHE_ENABLED and DISCOVERY_CACHE_PATH from getenv when $_ENV lacks them`
- [x] `it does not use the discovery cache when APP_ENV is local`
- [x] `it does not use the discovery cache when MARKO_ENV is dev even if APP_ENV is production`
- [x] `it registers AppEnvironment as a shared container instance`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
