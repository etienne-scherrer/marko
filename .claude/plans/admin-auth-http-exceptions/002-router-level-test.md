# Task 002: Router-level rendering test

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove through a real `Router` that admin 401/403s are rendered by `ExceptionRenderer` in JSON and HTML, and that browser guests are still redirected.

## Context
- Related files: packages/admin-auth/tests/Feature/AdminAuthRouterTest.php (new)
- Patterns to follow: packages/authorization/tests/Feature/CanRouterTest.php

## Requirements (Test Descriptions)
- [x] `it renders a JSON 401 through the exception renderer for an unauthenticated request that wants JSON`
- [x] `it redirects an unauthenticated HTML request to the admin login through the router`
- [x] `it renders a JSON 403 through the exception renderer when the admin lacks the permission`
- [x] `it renders an HTML 403 page through the exception renderer when the admin lacks the permission`
- [x] `it never includes the required permission key in the 403 body`
- [x] `it lets a permitted admin reach the controller through the router`

## Acceptance Criteria
- All requirements have passing tests

## Wiring details
- Namespace the file `Marko\AdminAuth\Tests\Feature` and give fixture classes/helpers names that do not collide with the unit test's `TestController*`, `StubAdminConfig`, `createMiddleware()` (e.g. prefix with `Router`).
- Do NOT copy CanRouterTest's `createUnconfiguredRouter()` / `module.php` approach: `PermissionRegistryInterface` has no binding in `packages/admin-auth/module.php`, so autowiring throws `BindingException`. Build a `Container` and register explicitly:
  - `$container->instance(GuardInterface::class, $fakeGuard)` (`Marko\Testing\Fake\FakeGuard`)
  - `$container->instance(AdminConfigInterface::class, <stub with routePrefix '/admin'>)`
  - `$container->instance(PermissionRegistryInterface::class, new PermissionRegistry())`
- Attach the middleware as route middleware, matching production `#[Middleware(AdminAuthMiddleware::class)]`: `new RouteDefinition(method: 'GET', path: ..., controller: ..., action: ..., middleware: [AdminAuthMiddleware::class])`. Use one action with `#[RequiresPermission('posts.create')]`.
- Router populates `Request::controller()/action()` via `withRoute()` before middleware runs, so `#[RequiresPermission]` is evaluated — no manual `withRoute()` needed.
- Assert JSON bodies exactly: `['message' => 'Unauthorized.']` and `['message' => 'Forbidden.']`; HTML 403 asserts `Content-Type` contains `text/html` and body contains `403 Forbidden`.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
