# Task 001: Lazy factories in AuthorizationMiddleware and module binding

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the eager `GateInterface`/`GuardInterface` constructor arguments with typed `Closure` factories that are invoked only when the matched route has `#[Can]`, and memoize the results. Update the `module.php` binding to pass closures resolving from the container.

## Context
- Related files: packages/authorization/src/Middleware/AuthorizationMiddleware.php, packages/authorization/module.php, packages/authorization/tests/**
- Patterns to follow: `Closure(): X $factory` in packages/pubsub-redis/src/Driver/SharedAmphpRedisSubscriber.php

## Requirements (Test Descriptions)
- [x] `it never calls the gate or guard factory for a route without Can`
- [x] `it never calls the gate or guard factory for an unmatched request`
- [x] `it resolves the gate and guard once across repeated Can requests`
- [x] `it returns 200 for a route without Can when no auth or session is configured`
- [x] `it enforces Can through the module binding with the configured guard`
- [x] `it never calls the gate factory when the guard reports a guest`
- [x] `it retries a factory that threw on the next Can request instead of caching the failure`

## Acceptance Criteria
- All requirements have passing tests
- Existing middleware/router/wiring tests updated and green
- Code follows code standards

## Implementation Notes
Constructor is now `__construct(Closure $gate, Closure $guard)` with `@param Closure(): GateInterface` / `Closure(): GuardInterface`. Private `gate(): GateInterface` / `guard(): GuardInterface` accessors memoize into nullable private properties, assigned only after the factory returns successfully (a throwing factory throws again on the next `#[Can]` request; never cache a failure). The return types make a wrong-typed factory fail loudly. Call `guard()` first; only call `gate()` after `check()` passes, so a 401 never builds the Gate. The binding closure captures the container and resolves `GateInterface`, `AuthorizationConfig` and `AuthManager` inside the factories, with no `$container->get()` outside them.

The "no auth or session is configured" test must go through the real `module.php` binding (`authorizationModule()['bindings'][AuthorizationMiddleware::class]($container)`, or bind all module bindings/singletons as `ModuleWiringTest` does) on a bare `Container` with no `AuthManager`, `AuthorizationConfig`, `ConfigRepositoryInterface` or session bound, so any eager resolution throws. Dispatch a non-`#[Can]` route through a real `Router` with the middleware as global middleware, and also an unmatched path. `Router::dispatch()` runs global middleware for unmatched requests too, so the unmatched path should still return a 404 and not a container or auth error.

**Existing tests that break:** `tests/Feature/CanRouterTest.php::createAuthorizedRouter()` registers `GuardInterface`/`GateInterface` instances and lets the Router autowire `AuthorizationMiddleware`. That no longer works with `Closure` params. Bind the middleware explicitly in the helper, e.g. `$container->bind(AuthorizationMiddleware::class, fn (): AuthorizationMiddleware => new AuthorizationMiddleware(gate: fn (): GateInterface => $gate, guard: fn (): GuardInterface => $guard))`. `tests/Unit/Middleware/AuthorizationMiddlewareTest.php` (constructs it directly around line 100) must wrap its doubles in closures. `ModuleWiringTest` should keep passing unchanged and proves the factories still use `authorization.default_guard`.
Done: spy-factory tests in `tests/Unit/Middleware/AuthorizationMiddlewareTest.php`; no-config Router tests (200 without `#[Can]`, 404 unmatched, loud `BindingException` on a `#[Can]` route) in `tests/Feature/CanRouterTest.php` using the real module bindings on a bare `Container`. The "enforces Can through the module binding with the configured guard" requirement is covered by the existing `ModuleWiringTest` cases, which pass unchanged against the lazy binding.
