# Plan: Lazy #[Can] Middleware

## Created
2026-10-05

## Status
completed

## Objective
Make `AuthorizationMiddleware` resolve the Gate and the guard lazily (typed `Closure` factories), only when the matched route carries `#[Can]`, so routes without the attribute never make the middleware resolve the Gate, AuthManager, guard or authorization config (issue #233, option B). Note: session driver modules still run `SessionMiddleware` globally; that is unaffected.

## Related Issues
Closes #233

## Discovery Notes
- `packages/authorization/module.php` builds `GateInterface` and the default guard eagerly inside the `AuthorizationMiddleware` binding; the middleware is global, so every request pays for (or fails on) the auth stack.
- The middleware only uses them after `resolveCanAttribute()` returns non-null.
- Precedent for typed lazy factories: `Closure(): X $factory` in `packages/pubsub-redis/src/Driver/SharedAmphpRedisSubscriber.php`.
- #222 (HttpException for guests) is merged; the middleware already throws instead of rendering.
- Option D (boot-time validation) is deferred until #173 (route cache) lands, as the issue recommends.

## Scope

### In Scope
- Constructor takes `Closure(): GateInterface $gate` and `Closure(): GuardInterface $guard`, resolved once on first `#[Can]` route and memoized
- `module.php` binding passes closures that resolve from the container on demand
- Tests: no-config 200 for routes without `#[Can]`; spy factories never called without `#[Can]`; resolved once and enforced with `#[Can]`
- Docs page + README note on cost and requirements

### Out of Scope
- Option C (route-attached middleware) and option D (boot-time check, depends on #173)
- Any change to marko/authentication

## Success Criteria
- [x] Route without `#[Can]` returns 200 with no auth/session configuration
- [x] Gate and guard factories never called for routes without `#[Can]`
- [x] `#[Can]` routes resolve gate and guard once (failures not cached) and enforce as before
- [x] `CanRouterTest`, `AuthorizationMiddlewareTest` and `ModuleWiringTest` updated and green
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Lazy factories in AuthorizationMiddleware and module binding | - | completed |
| 002 | Document lazy #[Can] enforcement | 001 | completed |

## Architecture Notes
Typed `Closure(): T` factories (not the raw container) keep the middleware free of service location while deferring construction. The middleware stays a singleton; resolved services are memoized on the instance, matching the previous per-worker lifetime.

## Risks & Mitigations
- Misconfiguration now surfaces on the first `#[Can]` request instead of the first request: documented; option D will add a boot-time check after #173.
