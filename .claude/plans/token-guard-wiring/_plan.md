# Plan: Token Guard Wiring

## Created
2026-10-05

## Status
completed

## Objective
Make `AuthManager` resolve guards through an explicit driver registry so `marko/authentication-token`'s real `TokenGuard` serves the `token` driver, make stateless guards fail loudly on stateful methods, and add token lifecycle events.

## Related Issues
Closes #232

## Discovery Notes
- `AuthManager::createGuard()` hard-codes `session`/`token` in a `match`; the `token` arm builds the minimal `Marko\Authentication\Guard\TokenGuard`, which nothing ever hands request headers to (it can never authenticate through AuthManager).
- `marko/authentication-token` declares a `'guards'` module key that nothing reads.
- The token package's `TokenGuard` takes a `Request` in its constructor, but `Request` is not in the container, so autowiring it gives an empty request. A request holder fed by a global middleware (same pattern as `RequestCookieJar` + `QueuedCookiesMiddleware`) is needed.
- `AuthMiddleware` (after #222) skips the login redirect via `instanceof TokenGuard` (the minimal class). Coordinator: replace with an explicit stateless-guard contract, also return 401 when `Request::wantsJson()`, optionally add `WWW-Authenticate`.
- Module `boot` closures run after bindings; constructing `AuthManager` at boot would force `UserProviderInterface`/session resolution, so the registry must be a dependency-free singleton and driver factories must resolve lazily.
- Issue is labelled `question` for events: implement the recommendation (token lifecycle events, no fake login events).
- #233 edits `marko/authorization` in parallel; authorization source stays untouched.

## Scope

### In Scope
- `GuardDriverRegistry` (`extend()`) consulted by `AuthManager`; built-in `session` driver; loud error naming `marko/authentication-token` for an unregistered `token` driver.
- Delete `Marko\Authentication\Guard\TokenGuard` (breaking).
- `StatelessGuardInterface` (WWW-Authenticate challenge); `AuthMiddleware` uses it and `wantsJson()`.
- Token package: `CurrentRequest` holder + global `TokenRequestMiddleware`, `TokenGuardFactory`, module `boot` registration, delete `'guards'` key.
- Token guard: configurable name, stateful methods throw `StatelessGuardException`, failed-auth event.
- Events: `TokenCreatedEvent`, `TokenRevokedEvent`, `AllTokensRevokedEvent`, `TokenAuthenticationFailedEvent` (+ `TokenFailureReason` enum). Never the plain token.
- Docs pages + READMEs.

### Out of Scope
- Splitting `GuardInterface` into stateful/stateless interfaces (exit criteria require the token guard's stateful methods to exist and throw).
- Changes to `marko/authorization` source (#233 in flight).
- A `TokenRepositoryInterface` implementation.

## Success Criteria
- [x] `AuthManager::guard()` with driver `token` returns `Marko\AuthenticationToken\Guard\TokenGuard` when the token module is booted; clear error naming the package otherwise
- [x] `'guards'` module key removed; registry documented and tested with a custom driver
- [x] Expired token → 401 through `AuthMiddleware` and `#[Can]`
- [x] Stateful methods on the token guard throw helpful errors
- [x] Token events dispatched, never include the plain-text token
- [x] Docs + READMEs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Guard driver registry in AuthManager; delete minimal TokenGuard | - | completed |
| 002 | StatelessGuardInterface + AuthMiddleware 401 rules | 001 | completed |
| 003 | Token lifecycle events dispatched by TokenManager | - | completed |
| 004 | Token guard: request holder, stateless contract, throwing stateful methods, failure event | 002, 003 | completed |
| 005 | Token module wiring + end-to-end expiry tests | 001, 002, 004 | completed |
| 006 | Docs pages, READMEs, dependent tests/docs | 001-005 | completed |

## Architecture Notes
- Registry lives in `marko/authentication` as a singleton with no dependencies so module `boot` callbacks can register drivers cheaply; factories receive `(string $name, array $config, UserProviderInterface $provider)`.
- Registered drivers win over the built-in `session` driver, so apps can override it too.
- The token guard re-resolves its token whenever the current `Request` instance changes, so a cached guard stays correct across requests in long-running workers and the test client.

## Risks & Mitigations
- Global middleware order: `TokenRequestMiddleware` must run before authorization's global `AuthorizationMiddleware`: token module declares `sequence.before: marko/authorization`. cors must list `marko/authentication-token` in its `sequence.before`, or `CorsGlobalTest` fails.
- Token leakage: the existing `ExpiredTokenException`/`InvalidTokenException::forToken()` embed the raw token in context; task 004 removes that.
- Long-running workers: `TokenRequestMiddleware` clears `CurrentRequest` in `finally`; no request means guest.
- Breaking changes (class removal, TokenGuard constructor): PR labelled `breaking`, documented.
