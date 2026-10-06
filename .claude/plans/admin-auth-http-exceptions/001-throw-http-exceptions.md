# Task 001: Throw HttpException from AdminAuthMiddleware

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the hand-built 401/403 responses with thrown `HttpException`s and use `Request::wantsJson()` for the redirect decision.

## Context
- Related files: packages/admin-auth/src/Middleware/AdminAuthMiddleware.php, packages/admin-auth/tests/Unit/Middleware/AdminAuthMiddlewareTest.php, packages/admin-auth/composer.json, packages/admin-auth/tests/ComposerDependenciesTest.php
- Patterns to follow: packages/authentication/src/Middleware/AuthMiddleware.php

## Requirements (Test Descriptions)
- [x] `it throws a 401 HttpException for an unauthenticated request that wants JSON`
- [x] `it redirects an unauthenticated browser request to the admin login`
- [x] `it throws a 403 HttpException when the user lacks the required permission`
- [x] `it throws a 403 HttpException when the authenticated user is not an admin user on a gated route`
- [x] `it keeps the required permission key out of the 403 message and puts it in the context`
- [x] `it requires marko/routing in composer.json`

## Acceptance Criteria
- All requirements have passing tests
- `unauthorizedResponse()`, `forbiddenResponse()` and `isJsonRequest()` are deleted
- Existing tests in `AdminAuthMiddlewareTest.php` that assert on a returned 401/403 `Response` (lines ~101, 151, 221, 241, 311, 350, 388, 401 — including the `'Forbidden'` body and `['error' => ...]` JSON assertions) are converted to `expect(fn () => $middleware->handle(...))->toThrow(...)` assertions or folded into the new requirements above; no test may still expect a returned 401/403 or the old `error` key. Pass-through and redirect tests stay as-is.
- No unused imports (`JsonException`) remain; `handle()`'s `@throws` docblock reads `ReflectionException|HttpException`. `composer phpstan` and `./vendor/bin/phpcs` are clean for touched files.

## Construction details (do not deviate)
- 401: `throw HttpException::unauthorized('Unauthorized.');` — only when `$request->wantsJson()`; otherwise `return Response::redirect($this->adminConfig->getRoutePrefix() . '/login');`
- 403: `HttpException::forbidden()` cannot take a `context` and its default message has no trailing period, so use the full constructor: `throw new HttpException(statusCode: 403, message: 'Forbidden.', context: "Admin user lacks required permission '$requiredPermission'");` (for the non-`AdminUserInterface` branch, the context should say the authenticated user is not an admin user). The message must never contain the permission key.
- Composer: add `"marko/routing": "self.version"` to `require` and assert it in `tests/ComposerDependenciesTest.php` (extending the existing dependency assertion chain is fine).

## Implementation Notes
(Left blank - filled in by programmer during implementation)
