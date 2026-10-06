# Devil's Advocate Review: can-www-authenticate

## Critical (Must fix before building)

### C1. Task 003: the router test with the real TokenGuard can pass without ever reading the token
`TokenGuard` reads the `Authorization` header only through `CurrentRequest`, and only `TokenRequestMiddleware` (marko/authentication-token) sets it. The router does not. If the test registers only `AuthorizationMiddleware` as global middleware, `CurrentRequest::get()` returns `null`, `lookUpToken()` returns `null`, and every request counts as a guest. The "unknown token" test would then pass even if the header is never read, so it proves nothing about the token path.
**Fix (applied to 003):** register `TokenRequestMiddleware::class` before `AuthorizationMiddleware::class` in `globalMiddleware`, share one `CurrentRequest` instance between the middleware and the guard, and add a positive control: a valid token on an allowed `#[Can]` route returns 200. That proves the header reaches the guard.

## Important (Should fix before building)

### I1. Tasks 001/002: work already exists in the worktree
`packages/authentication/src/Exceptions/UnauthenticatedException.php`, `packages/authentication/tests/Exceptions/UnauthenticatedExceptionTest.php` (all 5 of task 001's requirements) and the two new `AuthMiddlewareTest` cases from task 002 are already present. Workers on 001/002 should check what is there and finish it, not create a second copy.
**Fix (applied to _plan.md discovery notes, 001, 002).**

### I2. Task 005 misses two doc locations and the failure-table exception class
- `packages/docs-markdown/docs/guides/authentication.md` lines 114 and 223 say only `AuthMiddleware` sends `WWW-Authenticate`. Neither line is in task 005.
- The "Exception" column in the `authorization.md` failure table (line 255) still names `Marko\Routing\Exceptions\HttpException` for the `#[Can]` guest 401. It should name `UnauthenticatedException`.
- `authentication.md` line 466 (AuthMiddleware section) and `admin-auth.md` line ~44 describe the 401 as a plain `HttpException`.
**Fix (applied to 005).**

### I3. Task 004: the stateless-challenge requirement only applies on the JSON branch
`AdminAuthMiddleware` redirects any browser guest before it reaches the throw, whatever the guard (redirect changes are out of scope). The test must send a JSON request (`Accept: application/json`) with a stateless guard. Otherwise it hits the redirect and the worker may "fix" that by changing redirect behaviour. admin-auth cannot use `Marko\Authentication\Tests\Fixtures\StatelessFakeGuard` (it is another package's test fixture), so define an inline `FakeGuard` subclass that implements `StatelessGuardInterface`, with a unique, namespaced name.
**Fix (applied to 004).**

### I4. Tasks 003/004: class docblocks and `@throws` describe the old exception
`AuthorizationMiddleware`'s docblock says "a guest gets an HttpException (401)". `AdminAuthMiddleware`'s says "gets a 401 HttpException". After the change, both should name `UnauthenticatedException` and say that a stateless guard's challenge is included. `@throws HttpException` stays valid for PHPStan (it is a subclass), but the docblock should be accurate.
**Fix (applied to 003, 004).**

## Minor (Nice to address)
- `UnauthenticatedException` inherits `HttpException::unauthorized()` etc. Because they use `new self`, which binds to the declaring class, `UnauthenticatedException::unauthorized()` returns a plain `HttpException`. This is harmless but surprising. A docblock note is enough.
- Nothing stops `new UnauthenticatedException(500)`. You could lock the status code, but that is not needed for this issue.
- The test file sits at `packages/authentication/tests/Exceptions/`, while the other tests in the package mostly live under `tests/Unit/`. It is still picked up by the `packages/*/tests` testsuite glob.
- New classes and functions in test files (task 003's fake token repository, task 004's stateless guard) must be namespaced and uniquely named. Pest loads many files into one process, and `CanRouterTest` already declares namespaced helper functions such as `createAuthorizedRouter`.

## Questions for the Team
- `AdminAuthMiddleware` still redirects a browser guest when its guard is stateless. That is out of scope here. Should it get a follow-up issue so it matches `AuthMiddleware` (stateless guards never redirect)?
