# Task 007: Remember-me and auth events (#168)

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Convert the #168 todos into `tests/Integration/App/AuthTest.php`. Add fixture routes to log in with remember, log out and report the current user, plus an observer that records LoginEvent/LogoutEvent.

## Context
- Related files: `Fixture/app/integration/module.php`, `IntegrationController.php`, `packages/authentication/src/Guard/SessionGuard.php`
- Between requests, reset every resolved `ResettableInterface` (worker-style) so the guard cannot answer from its in-memory cache. Use the existing `Marko\Core\RequestStateResetter::reset()`; don't hand-roll it.

## Implementation Notes
- In fixture `module.php`, move `UserProviderInterface` into `singletons` (today it is a plain binding that survives only because the singleton `AuthManager` holds it). `FakeUserProvider` keeps remember tokens in memory.
- The cookie is `remember_session` (config prefix `remember_` + guard name `session`). The re-auth request carries only that cookie, with no session cookie.
- The observer must record events somewhere that survives across requests: a singleton recorder resolved from the container, or a file under `{project}/storage`.
- If login is POST, use the `post` parameter on `integrationRequest()` (added here if task 008 has not added it yet).

## Requirements (Test Descriptions)
- [x] `it issues a remember-me cookie that re-authenticates a later request`
- [x] `it dispatches login and logout events`

## Acceptance Criteria
- Tests pass

## Implementation Notes
- The remember-me test exposed a real worker-mode leak: a request with no cookies after an authenticated one was still authenticated. `AuthManager` builds its guards with `new`, so `RequestStateResetter` only reached a guard once something resolved `GuardInterface` from the container; a controller using `AuthManager::guard()` kept the previous request's user. Fixed test-first: `AuthManager` implements `ResettableInterface` and resets every cached resettable guard (unit tests in `packages/authentication/tests/Unit/AuthManagerTest.php`). Docs: authentication.md API reference, roadrunner-state-leaks.md, architecture.md implementor list.
