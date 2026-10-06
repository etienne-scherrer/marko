# Task 002: AuthorizationMiddleware throws instead of building responses

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`AuthorizationMiddleware` throws `HttpException::unauthorized()` for a guest and `AuthorizationException::forbidden()` for a denial, so the pipeline's `ExceptionRenderer` renders both. Delete `unauthorizedResponse()`, `forbiddenResponse()` and `isJsonRequest()`.

## Context
- Related files: packages/authorization/src/Middleware/AuthorizationMiddleware.php, packages/authorization/tests/Unit/Middleware/AuthorizationMiddlewareTest.php, packages/authorization/tests/Feature/CanRouterTest.php

## Requirements (Test Descriptions)
- [x] `it throws a 401 HttpException when user is not authenticated`
- [x] `it throws a 403 AuthorizationException when gate denies the ability`
- [x] `it carries the ability and entity class on the thrown AuthorizationException`
- [x] `it allows request when gate allows the ability`
- [x] `it reuses the resolved Can attribute for repeated requests to the same action` (rewritten to expect two throws)

## Acceptance Criteria
- All requirements have passing tests
- No private response helpers remain in the middleware
- The whole `packages/authorization` suite is green, including the existing `CanRouterTest`

## Implementation Notes
- Existing `AuthorizationMiddlewareTest` cases that assert `statusCode()`/`body()` on a returned 401/403 (lines ~132-227 and the cache test at ~264) must be rewritten to expect the thrown exception. Remove obsolete JSON/plain-body variants: the renderer now owns them.
- The existing `CanRouterTest` assertions on the old bodies (`'Forbidden'` line 177, `'Unauthorized'` line 195, `'{"error":"Unauthorized"}'` line 204) break once this task lands. Update them here to the renderer output: status code, `Content-Type`, and `{"message":"Unauthorized"}` / `"message":"Forbidden."`. Task 004 adds new tests only.
- Replace `@throws JsonException` with `@throws HttpException|AuthorizationException`, and drop the unused `JsonException` import.
