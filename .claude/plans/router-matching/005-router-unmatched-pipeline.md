# Task 005: Router pipeline for unmatched requests, 404/405, automatic OPTIONS, HEAD

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Build the global-middleware pipeline for every request. The terminal handler for unmatched requests throws `HttpException::notFound()` / `methodNotAllowed()`, or returns an automatic 204 for OPTIONS. Strip the body from every HEAD response.

## Context
- Related files: packages/routing/src/Router.php, tests/RouterTest.php, tests/HttpExceptionHandlingTest.php
- Uses `RouteMatcherInterface::allowedMethods()` (task 002; `[]` means 404) for 405 and the automatic OPTIONS `Allow` header, and `Response::withoutBody()` (task 004) for HEAD.
- Unmatched requests run global middleware only (never route middleware); the request keeps a null controller/action.
- Body stripping for HEAD happens on the response returned by the whole pipeline, so rendered 404/405 bodies are stripped too.
- Existing tests that assert the old bare body must be updated to the `ExceptionRenderer` output: packages/routing/tests/RouterTest.php:81 and packages/routing/tests/Integration/DemoRoutingTest.php:112 (`body() === 'Not Found'`). Roadrunner `WorkerRequestHandlerTest` only checks status and should keep passing once `FakeRouteMatcher::allowedMethods()` returns `[]`.

## Requirements (Test Descriptions)
- [x] `it returns 405 with Allow POST, OPTIONS for HEAD to a POST-only path`
- [x] `it returns 404 for OPTIONS on an unknown path`
- [x] `it strips the body from a HEAD 404 response`
- [x] `it does not run route middleware for unmatched requests`
- [x] `it returns 405 with an Allow header when the path matches another method`
- [x] `it returns a JSON 404 for an unknown path when the client accepts JSON`
- [x] `it runs global middleware on 404 and 405 responses`
- [x] `it answers OPTIONS automatically with 204 and an Allow header`
- [x] `it dispatches an explicit OPTIONS route instead of the automatic response`
- [x] `it returns the GET status and headers with an empty body for HEAD`
- [x] `it dispatches an explicit HEAD route instead of the GET fallback`
- [x] `it leaves the request controller null for unmatched requests`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
