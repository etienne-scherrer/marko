# Task 005: Token module wiring + end-to-end expiry tests

**Status**: completed
**Depends on**: 001, 002, 004
**Retry count**: 0

## Description
`marko/authentication-token`'s module.php drops the dead `'guards'` key, registers the `token` driver in `GuardDriverRegistry` from `boot` (resolving the factory lazily), registers `CurrentRequest` as a singleton, adds `TokenRequestMiddleware` as global middleware and sequences before `marko/authorization`.

## Context
- Related files: packages/authentication-token/module.php, packages/authorization/src/Middleware/AuthorizationMiddleware.php (read-only)

## Requirements (Test Descriptions)
- [x] `it no longer declares the unused guards module key`
- [x] `it makes AuthManager return the authentication-token TokenGuard for the token driver`
- [x] `it returns 401 from AuthMiddleware for an expired token`
- [x] `it returns 401 from the #[Can] authorization middleware for an expired token`
- [x] `it lets a valid token through AuthMiddleware`
- [x] `it sends WWW-Authenticate: Bearer on the AuthMiddleware 401 for an expired token`
- [x] `it orders TokenRequestMiddleware before AuthorizationMiddleware in the global middleware` (follow `packages/authorization/tests/Unit/ModuleWiringTest.php`)
- [x] Add `'marko/authentication-token'` to `packages/cors/module.php` `sequence.before`. `packages/cors/tests/CorsGlobalTest.php` requires every module that declares `globalMiddleware` to be listed there, so it fails without this

## Implementation Notes (from review)
- `composer.json`: add `marko/routing` to `require` (the new middleware implements `MiddlewareInterface`; `TokenGuard` already uses `Request`) and `marko/authorization` to `require-dev` for the `#[Can]` test.
- No `TokenRepositoryInterface` implementation exists (out of scope). E2E tests use an in-memory repository fixture under `tests/Fixtures/` plus `FakeClock`.
- Run the real module.php `boot` closure through `$container->call()`; don't re-register the driver by hand. Build the Router with `globalMiddleware: [TokenRequestMiddleware::class, AuthorizationMiddleware::class]` (pattern: `packages/authorization/tests/Feature/CanRouterTest.php`).

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
