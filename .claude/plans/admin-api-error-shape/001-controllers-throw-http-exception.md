# Task 001: Controllers Throw HttpException, Remove ApiResponse Error Helpers

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace `ApiResponse` error responses in `SectionController` and `MeController` with thrown `HttpException`s, then delete `ApiResponse::error()`, `notFound()`, `forbidden()` and `unauthorized()`.

## Context
- Related files: packages/admin-api/src/ApiResponse.php, packages/admin-api/src/Controller/SectionController.php, packages/admin-api/src/Controller/MeController.php, their unit tests
- Patterns to follow: `AdminAuthMiddleware::forbidden()` (generic client message, detail in `context`)

## Requirements (Test Descriptions)
- [x] `it throws a 404 HttpException for an unknown section`
- [x] `it throws a 404 HttpException when the user cannot access any menu item in the section`
- [x] `it throws a 403 HttpException when the authenticated user is not an admin user`
- [x] `it no longer exposes the error helpers`
- [x] `it keeps the success, created and paginated helpers`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
MeController's non-admin branch throws 403 `Forbidden.` (matching AdminAuthMiddleware) rather than 401, since the user is authenticated. Use `new HttpException(statusCode: 403, message: 'Forbidden.', context: '...')` — the static `HttpException::forbidden()` takes no `context` argument.

Every caller of the removed helpers must be updated in this task, or `composer test`/`composer phpstan` will break:
- `packages/admin-api/tests/Unit/Config/AdminApiConfigTest.php:77` (`returns a JSON 401 body from ApiResponse::unauthorized`) — delete this test.
- `packages/admin-api/tests/Unit/ApiResponseTest.php` — delete the `error`/`notFound`/`forbidden`/`unauthorized` tests.
- `SectionControllerTest.php` (lines ~258, ~293, ~435) and `MeControllerTest.php` (lines ~80, ~113) assert on `errors[0].message` from returned responses; rewrite them as `->toThrow(HttpException::class)` assertions (check status code and message).
- Remove the now-unused `ApiResponse` import from `MeController` only if no success call remains (it still uses `success()`, so keep it). Drop the `@throws JsonException`-only paths if they change.
- Final check: `grep -rn "ApiResponse::\(error\|notFound\|forbidden\|unauthorized\)" packages/` returns nothing outside docs (docs are task 003).
