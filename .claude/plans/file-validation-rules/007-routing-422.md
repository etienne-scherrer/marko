# Task 007: Routing-level 422 test

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Prove that file rule failures thrown from a controller become a 422 response with field-keyed errors through `Router::handle()`.

## Context
- Test location: `packages/validation/tests/Integration/FileValidationRoutingTest.php`, with its own namespace (e.g. `Marko\Validation\Tests\Integration\FileValidationRouting`). Not in routing: `marko/routing` does not require-dev `marko/validation`, but validation does require-dev routing.
- Patterns to follow: packages/routing/tests/HttpExceptionHandlingTest.php. Copy its router boot setup into the new file; its `bootRouter()` is namespaced to the routing test and cannot be imported.
- Build the request with `new Request(server: ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/upload', 'HTTP_ACCEPT' => 'application/json'], files: ['avatar' => new UploadedFile(...)])`. The controller validates `[...$request->post(), ...$request->files()]` with `validateOrFail()`.
- `ValidationException` implements `HttpExceptionInterface`, so the routing `ExceptionRenderer` turns it into a 422. It renders JSON only when the request asks for it (hence the `Accept` header) and HTML otherwise. Assert the status and that the decoded body's `errors.avatar` holds the pinned message. No routing source changes are expected.

## Requirements (Test Descriptions)
- [x] `it returns 422 with the file errors keyed by field`
- [x] `it passes a valid upload through to the controller`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
