# Plan: #[Can] Authorization Enforcement

## Created
2026-10-05

## Status
completed

## Objective
Make the `#[Can]` attribute actually enforce authorization: the middleware reads the matched route from the request, runs globally once `marko/authorization` is installed, and is ordered after the session middleware.

## Related Issues
Closes #167

## Discovery Notes
- `AuthorizationMiddleware` took `$controller`/`$action` as nullable constructor scalars. The container fills them with `null`, so `#[Can]` was never read and every request passed (fail-open).
- `Router::handle()` already calls `$request->withRoute($controller, $action)` before the pipeline; `Request::controller()`/`action()` expose it.
- `packages/authorization/module.php` registered no `globalMiddleware`.
- `SessionMiddleware` is registered globally by the session driver modules (`marko/session-file`, `marko/session-database`); `sequence.after` hints are soft and ignore uninstalled modules.
- `GateInterface` is built with the guard named by `authorization.default_guard`, while the middleware was autowired with the authentication default `GuardInterface`. The 401 check could therefore consult a different guard than the gate.

## Scope

### In Scope
- Read controller/action from the request; remove constructor scalars
- Class-level `#[Can]` (method-level overrides it)
- Per `controller::action` attribute cache (immutable data, safe for long-running workers)
- Global registration + session ordering in `module.php`
- Middleware guard matches the gate's guard (`authorization.default_guard`)
- Router-level regression tests through real `Router` + `MiddlewarePipeline` + `Container`
- Docs page and README updates

### Out of Scope
- Route model binding / instance-level checks in the middleware (explicit `$gate->authorize()` in the controller)
- Converting 401/403 responses to exceptions (follow-up after #169)

## Success Criteria
- [x] `#[Can]` route returns 403 when denied and 200 when allowed through the real router
- [x] Unauthenticated `#[Can]` request returns 401 (`{"error":"Unauthorized"}` for JSON)
- [x] Routes without `#[Can]` pass through untouched
- [x] Class-level `#[Can]` applies to all actions; method-level overrides it
- [x] Session middleware ordered before authorization middleware
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Middleware reads route from request, class-level #[Can], cache | - | completed |
| 002 | Global registration, session ordering, guard binding | 001 | completed |
| 003 | Docs page and README | 001, 002 | completed |

## Architecture Notes
- `AuthorizationMiddleware` is no longer a `readonly class` (it holds a mutable attribute cache); its injected dependencies are individually `readonly`.
- Registered as a singleton so the reflection cache survives across requests in long-running workers. The cache holds only immutable attribute data, so it needs no `ResettableInterface`.
- No new dependency from `marko/core` on `marko/authorization`.

## Risks & Mitigations
- Global middleware now constructs the gate/guard on every request: acceptable because `marko/authorization` already requires `marko/authentication`; misconfiguration fails loudly.
- Session ordering relies on soft `after` hints: verified with a `DependencyResolver` + `GlobalMiddlewareResolver` test.
