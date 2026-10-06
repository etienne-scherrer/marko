# Task 004: AdminAuthMiddleware uses the factory

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`AdminAuthMiddleware` throws `UnauthenticatedException::forGuard()` for a JSON guest, so all framework 401s for a guest are built the same way. Browser redirect to `{prefix}/login` is unchanged.

## Context
- Related files: packages/admin-auth/src/Middleware/AdminAuthMiddleware.php, packages/admin-auth/tests/Unit/Middleware/

## Requirements (Test Descriptions)
- [x] `it throws an UnauthenticatedException for an unauthenticated JSON request`
- [x] `it adds the guard's WWW-Authenticate challenge to the JSON 401 when the admin guard is stateless`
- [x] existing redirect and 403 tests still pass
- [x] Update the class docblock: an unauthenticated JSON request gets an `UnauthenticatedException` (401), carrying the guard's challenge when the guard is stateless

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- The challenge test MUST send a JSON request (e.g. `HTTP_ACCEPT => 'application/json'`). A browser guest is redirected to `{prefix}/login` before the throw is reached, whatever the guard. Do NOT change that redirect behaviour (out of scope).
- Do not import `Marko\Authentication\Tests\Fixtures\StatelessFakeGuard`, because it is another package's test fixture. Define an inline, namespaced, uniquely named `FakeGuard` subclass that implements `Marko\Authentication\Contracts\StatelessGuardInterface` (`getChallenge(): string` returns `'Bearer'`). marko/testing is already in admin-auth's require-dev.
- The existing tests that use `toThrow(HttpException::class)` keep passing, because UnauthenticatedException is a subclass.
