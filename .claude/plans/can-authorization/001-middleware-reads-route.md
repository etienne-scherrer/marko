# Task 001: Middleware Reads Route From Request

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Remove the `$controller`/`$action` constructor parameters from `AuthorizationMiddleware` and read the matched route from `Request::controller()`/`Request::action()`. Support class-level `#[Can]` with method-level override, and cache the resolved attribute per `controller::action`.

## Context
- Related files: `packages/authorization/src/Middleware/AuthorizationMiddleware.php`, `packages/authorization/src/Attributes/Can.php`, `packages/routing/src/Router.php`
- Patterns to follow: existing middleware tests; real `Router` + `RouteMatcher` + `Container`

## Requirements (Test Descriptions)
- [x] `it returns 403 through the router when the gate denies a Can ability`
- [x] `it returns 200 through the router when the gate allows a Can ability`
- [x] `it returns plain 401 through the router for unauthenticated requests`
- [x] `it returns JSON 401 through the router for unauthenticated JSON requests`
- [x] `it passes through when the request has no matched route` (controller/action null; must not reach `ReflectionMethod`)
- [x] `it passes routes without Can through the global middleware untouched`
- [x] `it applies a class-level Can to every action`
- [x] `it lets a method-level Can override the class-level Can`
- [x] `it targets classes and methods`
- [x] Existing middleware tests no longer pass controller/action via the constructor

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Tests in `packages/authorization/tests/Feature/CanRouterTest.php` use the real `Container`, `RouteMatcher` and `Router`, with `AuthorizationMiddleware` as global middleware, so the container autowires the middleware exactly as in production.
- Unit tests now build requests with `->withRoute(...)`.
