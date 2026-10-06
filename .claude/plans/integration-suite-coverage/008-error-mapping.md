# Task 008: Exception mapping 422/404/419 (#169)

**Status**: completed
**Depends on**: 007
**Retry count**: 0

## Description
Convert the #169 todos into `tests/Integration/App/ErrorMappingTest.php`. Add `marko/validation` and `marko/security` to the fixture and routes that validate, call `findOrFail()` and sit behind `CsrfMiddleware`.

## Context
- Related files: `tests/Integration/App/Helpers.php` (INTEGRATION_MODULES, integrationRequest), fixture controllers

## Requirements (Test Descriptions)
- [x] `it answers a validation failure with a 422 JSON response`
- [x] `it answers a missing entity with 404`
- [x] `it answers a CSRF failure with 419`

## Acceptance Criteria
- Tests pass

## Implementation Notes
- `integrationRequest()` only takes `$cookies`/`$server`, and `Request` does not parse `REQUEST_URI` into the query. Add optional `query` and `post` parameters (passed to `new Request(query:, post:)`), unless task 007 already added them.
- The CSRF route must be POST (`CsrfMiddleware` lets GET through). Send a wrong token as `server: ['HTTP_X_CSRF_TOKEN' => 'wrong']`, and also cover the missing-token case.
- `marko/security` needs session + encryption (both already in the fixture) and registers no global middleware, so existing tests are unaffected. HarnessTest/BootTest iterate `INTEGRATION_MODULES`, so they pick up the additions automatically.
