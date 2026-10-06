# Devil's Advocate Review: token-guard-wiring

## Critical (Must fix before building)

1. **Task 005: adding global middleware breaks `CorsGlobalTest`.** `packages/cors/tests/CorsGlobalTest.php:50-69` asserts that every module declaring `globalMiddleware` is listed in cors's `sequence.before`. Once `marko/authentication-token` registers `TokenRequestMiddleware` globally, that test fails. Fix: task 005 adds `'marko/authentication-token'` to `packages/cors/module.php` `sequence.before`.
2. **Task 001 vs 006: deleting the minimal `TokenGuard` leaves the suite red until task 006.** `packages/admin-api/tests/Unit/Config/AdminApiConfigTest.php` constructs `Marko\Authentication\Guard\TokenGuard` and calls `setHeaders()` in 3 tests. That cleanup was assigned to task 006, so workers on 002-005 would hit unrelated failures. Fix: move it into task 001.
3. **Tasks 001/002 run in parallel on the same files.** Task 001 deletes the class that `AuthMiddleware.php` imports (`use ...Guard\TokenGuard`, plus `instanceof` at line 46, which PHPStan flags). Both tasks also edit `AuthMiddlewareTest.php`, where task 001's constructor change touches `new AuthManager(`. Fix: make 002 depend on 001.

## Important (Should fix before building)

4. **Task 004: the existing exceptions embed the plain-text token.** `ExpiredTokenException::forToken($token, ...)` and `InvalidTokenException::forToken($token)` put the raw bearer token into `context`, and `ExpiredTokenExceptionTest` asserts that. `TokenGuard::resolveTokenEntity()` builds the exception with `$rawToken`. This contradicts "never the plain token". Fix: remove the token from both exceptions (use the token id or nothing) and invert the test.
5. **Task 004: no behaviour defined when there is no current request** (CLI, queue jobs, a boot-time `guard()` call). Also, `CurrentRequest` is never cleared, so in RoadRunner workers a later non-HTTP use could authenticate against the previous request's token. Fix: no request means guest with no event, and `TokenRequestMiddleware` resets the holder in `finally`.
6. **Task 004: `StatelessGuardException` has no defined namespace.** Fix: `Marko\AuthenticationToken\Exceptions\StatelessGuardException extends AuthException`.
7. **Task 005: dependency and test fixtures not specified.** `composer.json` for the token package does not require `marko/routing`, but the new middleware implements `MiddlewareInterface`. The `#[Can]` test needs `marko/authorization` (require-dev). No `TokenRepositoryInterface` implementation exists (out of scope), so the e2e tests need an in-memory repository fixture. Fix: spell these out. Also assert `WWW-Authenticate: Bearer` and the global middleware order. Run the real module.php `boot` closure through `$container->call()`.
8. **Task 001: the default value can mask a wiring bug.** `AuthManager` takes `GuardDriverRegistry $guardDriverRegistry = new GuardDriverRegistry()`. The container autowires class-typed params, so this is fine today. But a direct `new AuthManager(...)` or a future container change would silently use a private registry, and token module registration would vanish. Fix: add a test that the container-resolved `AuthManager` uses the singleton registry.
9. **Task 006: incomplete docs list.** `docs/guides/authentication.md` and `docs/tutorials/build-an-admin-panel.md` also reference the token guard/driver.

## Minor (Nice to address)

- `GuardDriverRegistry::drivers()` is typed `list<string>`, but `array_keys` turns numeric-string driver names into ints.
- `TokenRevokedEvent` is dispatched even when the id does not exist (the repository `revoke()` returns void).
- Re-resolve-on-request-change should hold a reference to the last request so the object id is not reused.
- The authentication-token `token_expiration_days` config is still unused by `TokenManager`.

## Questions for the Team

- `#[Can]` 401s from `AuthorizationMiddleware` carry no `WWW-Authenticate` header. Adding it requires an authorization source change, which is deferred because #233 is in flight. Is that acceptable for this PR?
- Should a stateful guard's JSON 401 also send a `WWW-Authenticate` header? The plan says no, since there is no scheme for sessions.
