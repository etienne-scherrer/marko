# Task 004: Router-level and integration tests through ExceptionRenderer

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Prove end to end, through a real `Router`, that authorization failures render via `ExceptionRenderer`, and add an integration-suite row for `Gate::authorize()`.

## Context
- Related files: packages/authorization/tests/Feature/CanRouterTest.php, tests/Integration/App/ServicesTest.php, tests/Integration/App/Fixture/app/integration/src/Http/IntegrationController.php

## Requirements (Test Descriptions)
- [x] `it returns a JSON 401 through the renderer for a guest asking for application/vnd.api+json`
- [x] `it returns an HTML 401 through the renderer for a guest without a JSON Accept header`
- [x] `it returns 403 through the renderer when the gate denies a Can ability`
- [x] `it returns 403, not 500, when a controller calls Gate::authorize() for a denied ability`
- [x] `it never includes the ability or resource name in the 403 body`
- [x] integration: `it returns 403 when a controller calls Gate::authorize() for a denied ability`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Task 002 already updated the existing `CanRouterTest` assertions. Add only the new tests listed above. For the `Gate::authorize()` router test, add a controller action without `#[Can]` that calls `GateInterface::authorize()` (inject the Gate through the container in `createAuthorizedRouter()`).
- Integration: add a new route (e.g. `GET /admin/authorize`) to `IntegrationController` with NO `#[Can]`. Otherwise `AuthorizationMiddleware` throws first and `Gate::authorize()` is never exercised. Inject `GateInterface` and call `authorize('view-admin')`; the fixture gate denies it to everyone. A guest also gets 403 here, not 401, because the Gate has no notion of a guest. Assert 403, the body does not contain `view-admin`, and the body does not contain the controller's success text.
- Adding a route changes `tests/Integration/App/DiscoveryCacheTest.php`'s route snapshot (cold vs warm). Run it to confirm it still passes.
