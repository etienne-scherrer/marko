# Plan: Authorization HTTP Exceptions

## Created
2026-10-05

## Status
ready

## Objective
Route every authentication and authorization failure (`#[Can]`, `AuthMiddleware`, `Gate::authorize()`) through the routing pipeline's `ExceptionRenderer` so they render as 401/403 with content negotiation, instead of hand-built responses or a 500.

## Related Issues
Closes #222
Relates to #233, #232

## Discovery Notes
- `MiddlewarePipeline` already catches `HttpExceptionInterface` at the depth thrown and renders it via `ExceptionRenderer` (JSON for `application/json`/`+json` Accept or a JSON Content-Type with no Accept, HTML otherwise).
- `Marko\Routing\Exceptions\HttpException` provides `unauthorized()`/`forbidden()` factories; both `marko/authorization` and `marko/authentication` already require `marko/routing`.
- `Marko\Authorization\Exceptions\AuthorizationException` extends plain `Exception` and is thrown both for a denial (`Gate::authorize()`) and for developer misconfiguration (`PolicyRegistry::register()` duplicate policy, `Gate::callPolicy()` missing policy method, unused `missingPolicy()` factory). If the whole class became a 403, misconfiguration would be silently rendered as "Forbidden" — so misconfiguration moves to a new non-HTTP `PolicyException`.
- `Marko\Authentication\Exceptions\AuthorizationException` is never thrown in `packages/*/src` — delete it.
- Integration fixture already has `/admin` with `#[Can('view-admin')]` (gate denies everyone) and a `ServicesTest` row for 401/403.

## Scope

### In Scope
- `AuthorizationException` implements `HttpExceptionInterface` (403, no headers, body `{"message":"Forbidden."}` only) and extends `MarkoException`
- New `PolicyException` for policy misconfiguration (stays a 500)
- `AuthorizationMiddleware` throws `HttpException::unauthorized()` / `AuthorizationException::forbidden()`; private response helpers deleted
- `AuthMiddleware` throws `HttpException::unauthorized()` for a token guard (never redirects it); for other guards it keeps `redirectTo` redirects, otherwise throws 401. The `instanceof TokenGuard` short-circuit stays because `#[Middleware]` cannot pass `redirectTo: null`, so removing it would 302 every API route to /login
- Delete `Marko\Authentication\Exceptions\AuthorizationException`
- Router-level tests and an integration-suite row for `Gate::authorize()` → 403
- Docs: authorization.md, authentication.md, routing.md error table; READMEs if needed

### Out of Scope
- Lazy `#[Can]` resolution (#233)
- Token guard wiring / `WWW-Authenticate` for token guards (#232 removes the `TokenGuard` the header decision would key on; #232 must also replace `AuthMiddleware`'s token-guard short-circuit)
- `admin-auth` middleware responses

## Success Criteria
- [x] `#[Can]` + guest → 401 via the renderer (JSON for `application/vnd.api+json`, HTML otherwise)
- [x] Denied `#[Can]` → 403 via the renderer
- [x] Controller calling `Gate::authorize()` for a denied ability → 403 (router test + integration row)
- [x] `AuthMiddleware` returns 401 via the renderer for token guards; session guards redirect when `redirectTo` is set, otherwise 401 via the renderer
- [x] 403 body never contains the ability or resource name
- [x] Duplicate `AuthorizationException` removed
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | AuthorizationException as a 403 HTTP exception, PolicyException for misconfiguration | - | completed |
| 002 | AuthorizationMiddleware throws instead of building responses (also updates existing CanRouterTest assertions) | 001 | completed |
| 003 | AuthMiddleware throws 401 (token guard short-circuit kept); remove duplicate AuthorizationException | - | completed |
| 004 | New router-level and integration tests through ExceptionRenderer | 001, 002, 003 | completed |
| 005 | Docs and READMEs | 001, 002, 003, 004 | completed |

## Architecture Notes
- Throw, don't render: middleware never builds error responses; the pipeline renders `HttpExceptionInterface` once, so app `ExceptionRenderer` Preferences apply.
- Client-safe data only: `getResponseData()` returns a fixed message; ability/resource stay on the exception for logs.

## Risks & Mitigations
- Response body shape change (`{"error":"Forbidden"}` → `{"message":"Forbidden."}`) is visible to API clients: called out in PR as breaking.
- Code catching `AuthorizationException` for policy misconfiguration must now catch `PolicyException`: noted in PR.
