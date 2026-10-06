# Task 002: Router-Level Error Shape Test

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add a feature test in packages/admin-api that routes real `/admin/api/v1/*` requests through `Router` with `AdminAuthMiddleware`, proving every denial and controller error renders as `{"message": ...}`.

## Context
- Related files: packages/admin-auth/tests/Feature/AdminAuthRouterTest.php (pattern)
- New file: packages/admin-api/tests/Feature/AdminApiErrorShapeTest.php

## Requirements (Test Descriptions)
- [x] `it renders an unknown section as a JSON 404 with only a message`
- [x] `it renders a guest JSON request as a 401 with only a message`
- [x] `it renders a missing permission as a JSON 403 with only a message`
- [x] `it renders a non-admin user on the me endpoint as a JSON 403 with only a message`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Route the real `SectionController::show` and `MeController::me` via `RouteDefinition` with `middleware: [AdminAuthMiddleware::class]` (no attribute discovery in the test). Bind `AdminSectionRegistryInterface` (with no sections, or a test registry) in the container; get `PermissionRegistryInterface` by loading admin-auth's module.php — from admin-api the path is `dirname(__DIR__, 3) . '/admin-auth/module.php'`, NOT `dirname(__DIR__, 2)` as in the pattern file.
- No admin-api route carries `#[RequiresPermission]`, so the missing-permission 403 case needs a test-only controller under `/admin/api/v1/...` annotated with `#[RequiresPermission('...')]`, routed through the same middleware. Do not add a permission attribute to production controllers.
- Non-admin user case: use `FakeAuthenticatable` (marko/testing) set on `FakeGuard`; `/admin/api/v1/me` has no required permission, so the middleware passes it through and the controller's 403 is what renders.
- All requests send `HTTP_ACCEPT: application/json`. Assert `json_decode($body, true)` is exactly `['message' => ...]` (`toBe`, not `toHaveKey`) so a stray `errors` key fails the test.
- Use a distinct namespace (`Marko\AdminApi\Tests\Feature`) and helper function names so they do not collide with admin-auth's `createAdminRouter()` under parallel/combined runs.
