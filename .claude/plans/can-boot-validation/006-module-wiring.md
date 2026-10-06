# Task 006: Post-boot check wiring

**Status**: completed
**Depends on**: 004, 008
**Retry count**: 0

## Description
Run the #[Can] configuration check on live boots only, after every module boot callback, by observing the core `ApplicationBooted` event (task 008). It must not run from authorization's own `boot` callback. Guard drivers are registered in module `boot` callbacks (the documented `GuardDriverRegistry::extend` pattern, usually from an app module). Nothing sequences those modules before `marko/authorization`, so a check inside authorization's `boot` callback would reject valid custom drivers.

The observer's constructor takes only `CachedDiscovery` and `ContainerInterface`. It returns at once when `isCached()` is true, before it resolves `RouteCollection`, the validator, `AuthManager` or anything else. Do not add a `discovery` key (task 005 was removed).

## Context
- Related files: packages/authorization/module.php, packages/authorization/src/ (new observer), packages/authentication-token/module.php, packages/authentication-token/tests/Feature/TokenGuardWiringTest.php, packages/testing/src/Http/TestClient.php (actingAs/useGuard)
- Building a `token` guard at boot resolves `TokenGuardFactory` and so `TokenRepositoryInterface`. Update the authentication-token module.php comment ("booting never needs a TokenRepositoryInterface"). It is now needed on live boots when #[Can] routes exist.

## Requirements (Test Descriptions)
- [x] `it fails a live boot when a Can route exists and the guard cannot be built`
- [x] `it boots live without auth configuration when no route uses Can`
- [x] `it does not check a Can route that excludes AuthorizationMiddleware`
- [x] `it never reads routes or builds the Gate or guard on a cached boot`
- [x] `it passes the boot check when the default guard is the token driver` (bind a TokenRepositoryInterface in the test)
- [x] `it passes the boot check for a custom guard driver registered in an app module boot callback that is not sequenced before marko/authorization`
- [x] `it still authorizes a Can route for a user set with actingAs after a live boot` (regression: the Gate must not pin the boot-time guard)
- [x] `discovery:cache fails on the same misconfiguration` (through its live boot)
- [x] `TokenGuardWiringTest and other existing suites keep passing`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Observer Marko\Authorization\Observer\CheckCanRoutesOnBoot on ApplicationBooted; returns before resolving anything else when CachedDiscovery::isCached(). The feature test builds real temp projects (symlinked packages) and covers live boot, discovery:cache in a subprocess, cached boot (controller class never loaded, AuthManager never built), token driver, a custom driver from an app module booting after marko/authorization, and an actingAs-style guard swap.
