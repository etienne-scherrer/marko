# Task 001: Stop redirecting guests on a stateless guard in AdminAuthMiddleware

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Change `AdminAuthMiddleware::handle()` so a guest is only redirected to `{prefix}/login` when the guard is stateful and the request doesn't want JSON; every other guest gets `UnauthenticatedException::forGuard($this->guard)`. Update the class docblock to say a stateless guard never redirects.

## Context
- Related files: `packages/admin-auth/src/Middleware/AdminAuthMiddleware.php`, `packages/admin-auth/tests/Unit/Middleware/AdminAuthMiddlewareTest.php`
- Patterns to follow: `packages/authentication/src/Middleware/AuthMiddleware.php`; the existing `StatelessAdminGuard` fake in the test file

## Requirements (Test Descriptions)
- [x] `it throws a 401 instead of redirecting an unauthenticated browser request when the admin guard is stateless`
- [x] `it adds the guard's WWW-Authenticate challenge to the browser 401 when the admin guard is stateless`
- [x] `it throws a 401 for an unauthenticated request with no Accept header when the admin guard is stateless`
- [x] `it redirects an unauthenticated browser request to the admin login` (existing, still passes on a stateful guard)
- [x] `it adds the guard's WWW-Authenticate challenge to the JSON 401 when the admin guard is stateless` (existing, still passes)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No change to the session-guard redirect or the 403 path

## Implementation Notes
Added `StatelessGuardInterface` check to the redirect condition in `AdminAuthMiddleware::handle()`, mirroring `AuthMiddleware`, and rewrote the class docblock. Three new unit tests (stateless browser 401, its Bearer challenge, stateless no-Accept 401) failed first, then passed; all 202 admin-auth tests pass.
