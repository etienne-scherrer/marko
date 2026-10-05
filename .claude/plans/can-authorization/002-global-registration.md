# Task 002: Global Registration and Session Ordering

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Register `AuthorizationMiddleware` as global middleware in `packages/authorization/module.php`, order the module after the session driver modules, and build the middleware with the same guard the gate uses.

## Context
- Related files: `packages/authorization/module.php`, `packages/core/src/Module/DependencyResolver.php`, `packages/core/src/Module/GlobalMiddlewareResolver.php`, `packages/session-file/module.php`, `packages/session-database/module.php`

## Requirements (Test Descriptions)
- [x] `it registers AuthorizationMiddleware as global middleware`
- [x] `it sequences after the session driver modules`
- [x] `it orders the session middleware before the authorization middleware`
- [x] `it builds the middleware with the guard configured for authorization`
- [x] `it registers AuthorizationMiddleware as a singleton`

## Acceptance Criteria
- All requirements have passing tests
- No new dependency from `marko/core` on `marko/authorization`

## Implementation Notes
- The `AuthorizationMiddleware` binding closure constructs the middleware with the constructor from Task 001 (`gate`, `guard` only; no controller/action scalars), using `$container->get(AuthManager::class)->guard($config->defaultGuard())` for the guard.
- Guard test must be behavioral, not reflection into private properties. Set up the authentication default guard as authenticated and the `authorization.default_guard` guard as unauthenticated. Resolve the middleware through the module binding and call `handle()` with a request that has `->withRoute()` set to a `#[Can]` action. Assert 401.
- Ordering test loads both session driver `module.php` files and authorization's `module.php` through `ManifestParser`-equivalent manifests, then runs `DependencyResolver` and `GlobalMiddlewareResolver`.
