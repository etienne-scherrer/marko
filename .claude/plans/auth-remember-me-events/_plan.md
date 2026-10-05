# Plan: Remember-me and Auth Events Through AuthManager

## Created
2026-10-05

## Status
completed

## Objective
Make `login($user, remember: true)`, remember-cookie authentication, and the `LoginEvent` / `LogoutEvent` / `FailedLoginEvent` dispatches actually work for guards built by `AuthManager`, with cookies written through `Response::withCookie()`.

## Related Issues
Closes #168

## Discovery Notes
- `AuthManager::createSessionGuard()` passes only session, provider and name; the cookie jar, token manager and event dispatcher stay null and every use is null-guarded, so remember-me and events silently do nothing.
- The only `CookieJarInterface` implementation is `FakeCookieJar` in `marko/testing`.
- `Response::withCookie()` / `Request::cookie()` exist on develop; `SessionMiddleware` uses this response-decoration pattern.
- Global middleware order follows module load order (composer require + `sequence` hints; unknown modules in `after` are ignored).
- Bug found: `SessionGuard` stores a SHA-256 hash via `updateRememberToken()` but passes the plain token to `retrieveByRememberToken()`. Both shipped providers (`AdminUserProvider`, `FakeUserProvider`) compare the stored value to the given token, so remember-cookie login can never succeed with them. The existing guard tests work around this with inline providers.
- `RememberTokenStorageInterface` only backs `auth:clear-tokens`; the guard's actual storage is `UserProviderInterface::updateRememberToken()` plus the user's `getRememberToken()`.
- `TokenGuard` (in `marko/authentication`) has no-op `login()`/`logout()`/`attempt()` and dispatches nothing.
- `composer.json` of the package requires only `php` and `marko/core` but imports `marko/config`, `marko/session` and `marko/routing` classes.

- Execution note: the post-plan devils-advocate review and parallel TDD workers could not be spawned (subagent concurrency limit), so the tasks were executed sequentially in dependency order by the planning agent.

## Scope

### In Scope
- Remember cookie config (`authentication.remember.*`) and typed `AuthConfig` getters
- `RequestCookieJar` (request-backed reads, queued `Cookie` writes, resettable)
- `QueuedCookiesMiddleware` registered as global middleware
- `AuthManager` passes event dispatcher, cookie jar and configured `RememberTokenManager`
- Loud `AuthException` when remember-me is requested but cannot be stored
- Hashed token passed to `retrieveByRememberToken()`; `AdminUserProvider` compares with `hash_equals`
- Declared composer dependencies
- Docs page + README

### Out of Scope
- Event dispatch from `TokenGuard` (it has no login/logout semantics; noted as a follow-up)
- A database-backed `RememberTokenStorageInterface` implementation

## Success Criteria
- [ ] All exit criteria of #168 have passing tests
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Remember cookie config and AuthConfig getters | - | completed |
| 002 | RequestCookieJar | 001 | completed |
| 003 | QueuedCookiesMiddleware | 002 | completed |
| 004 | SessionGuard remember hardening | - | completed |
| 005 | AuthManager and module wiring | 001, 002, 003, 004 | completed |
| 006 | End-to-end pipeline and event tests | 005 | completed |
| 007 | Docs page and README | 006 | completed |

## Architecture Notes
- Cookies only via `Response::withCookie()`; never `setcookie()`.
- `CookieJarInterface` is a singleton so the guard and the middleware share one queue.
- Remember-cookie lifetime comes from `RememberTokenManager::lifetimeMinutes()` (built from config), removing the guard's hard-coded constant.

## Risks & Mitigations
- Middleware ordering vs. session drivers: `sequence.after` on the session driver modules so the queued-cookie middleware runs inside the session middleware.
- Worker-mode leakage: jar implements `ResettableInterface`.
