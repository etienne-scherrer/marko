# Task 001: Guard driver registry in AuthManager; delete minimal TokenGuard

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Authentication\Guard\GuardDriverRegistry` with `extend(string $driver, Closure $factory)`, `has()`, `drivers()` and `create()`. `AuthManager` takes it as a constructor dependency (singleton in module.php) and consults it before the built-in `session` driver. Delete `Marko\Authentication\Guard\TokenGuard` and its test; an unregistered `token` driver throws an `AuthException` naming `marko/authentication-token`.

## Context
- Related files: packages/authentication/src/AuthManager.php, src/Exceptions/AuthException.php, module.php, tests/Unit/AuthManagerTest.php, tests/Integration/AuthFlowIntegrationTest.php
- Patterns to follow: AuthException named constructors

## Requirements (Test Descriptions)
- [x] `it builds guards for a custom driver registered with extend`
- [x] `it passes the guard name, guard config and user provider to the driver factory`
- [x] `it lets a registered driver replace the built-in session driver`
- [x] `it throws an error naming marko/authentication-token when the token driver is not registered`
- [x] `it lists registered drivers when the guard driver is unknown`
- [x] `it registers the guard driver registry as a singleton`
- [x] `it gives the container-resolved AuthManager the singleton registry` (a driver extended on the container's registry is used by `$container->get(AuthManager::class)`)
- [x] Remove/rewrite the three tests in `packages/admin-api/tests/Unit/Config/AdminApiConfigTest.php` that construct the deleted `Marko\Authentication\Guard\TokenGuard` (and `setHeaders()`), so the full suite stays green after this task

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
