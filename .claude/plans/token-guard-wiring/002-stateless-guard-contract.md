# Task 002: StatelessGuardInterface + AuthMiddleware 401 rules

**Status**: completed
**Depends on**: 001 (001 deletes the `TokenGuard` that AuthMiddleware imports and changes `new AuthManager(` calls in AuthMiddlewareTest; serialize to avoid conflicting edits)
**Retry count**: 0

## Description
Add `Marko\Authentication\Contracts\StatelessGuardInterface extends GuardInterface` with `getChallenge(): string` (the `WWW-Authenticate` challenge). `AuthMiddleware` replaces its `instanceof TokenGuard` check: a stateless guard always gets a 401 carrying `WWW-Authenticate`; any guard gets a 401 instead of a redirect when the request wants JSON.

## Context
- Related files: packages/authentication/src/Middleware/AuthMiddleware.php, tests/Unit/Middleware/AuthMiddlewareTest.php
- `HttpException` accepts a headers array.

## Requirements (Test Descriptions)
- [x] `it throws 401 instead of redirecting for a stateless guard`
- [x] `it sends the stateless guard's challenge in the WWW-Authenticate header`
- [x] `it throws 401 instead of redirecting when the request wants JSON`
- [x] `it still redirects a guest on a stateful guard for an HTML request`
- [x] `it lets an authenticated request through a stateless guard`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
