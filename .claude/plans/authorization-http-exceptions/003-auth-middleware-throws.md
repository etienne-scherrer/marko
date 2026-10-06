# Task 003: AuthMiddleware throws 401; remove duplicate AuthorizationException

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`AuthMiddleware` throws `HttpException::unauthorized()` for a token guard, never redirecting it. For other guards it keeps the `redirectTo` redirect (a real response, not an error) and otherwise throws `HttpException::unauthorized()`. The `instanceof TokenGuard` branch stays, but throws instead of building `Response::json()`: content negotiation in the renderer decides JSON vs HTML. Delete the unused `Marko\Authentication\Exceptions\AuthorizationException`.

## Context
- Related files: packages/authentication/src/Middleware/AuthMiddleware.php, packages/authentication/src/Exceptions/AuthorizationException.php, packages/authentication/tests/Unit/Middleware/AuthMiddlewareTest.php, packages/authentication/tests/ExceptionTest.php

## Requirements (Test Descriptions)
- [x] `it redirects for web guard when unauthenticated`
- [x] `it throws a 401 HttpException when unauthenticated and redirectTo is null`
- [x] `it throws a 401 HttpException for a token guard even when redirectTo is set`
- [x] `it renders the 401 as JSON or HTML through the pipeline for session and token guards`
- [x] `it no longer ships Marko\Authentication\Exceptions\AuthorizationException`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Do NOT drop the token-guard short-circuit. `#[Middleware(AuthMiddleware::class)]` accepts only class strings, so the container always builds `AuthMiddleware` with the default `redirectTo: '/login'`. No route-group or middleware-argument mechanism exists to pass `redirectTo: null`. Without the short-circuit, every token-guard (API) route would return 302 → /login instead of 401.

Existing tests to update: `tests/Unit/Middleware/AuthMiddlewareTest.php` ('it returns 401 for API guard' and 'it supports specifying guard via parameter' assert on a returned JSON response → expect a thrown 401 `HttpException`; 'it blocks unauthenticated users' still gets a 302) and `tests/ExceptionTest.php` (remove the two `AuthorizationException` tests). Replace `@throws JsonException|AuthException` with `@throws HttpException|AuthException`.
