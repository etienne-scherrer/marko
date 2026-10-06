# Plan: Admin Auth HTTP Exceptions

## Created
2026-10-05

## Status
completed

## Objective
Make `AdminAuthMiddleware` throw `HttpException` for 401 (non-redirect) and 403 instead of hand-building error responses, so admin denials are rendered by `ExceptionRenderer` like every other HTTP error.

## Related Issues
Closes #254

## Discovery Notes
- `AdminAuthMiddleware` (`packages/admin-auth/src/Middleware/AdminAuthMiddleware.php`) builds `{"error":...}` JSON, a plain-text `Forbidden` body, and a redirect, with its own `str_contains($accept, 'application/json')` negotiation.
- `AuthMiddleware` (`packages/authentication`) is the pattern: redirect browser requests when `!$request->wantsJson()`, otherwise `throw HttpException::unauthorized('Unauthorized.')`.
- `HttpException` accepts a `context` string (log-only, never sent to clients); the permission key goes there.
- `packages/authorization/tests/Feature/CanRouterTest.php` is the pattern for a router-level test with a real `Router`, `RouteMatcher` and `Container`.
- `marko/admin-auth` already imports `Marko\Routing\*` but only gets `marko/routing` transitively through `marko/admin`; it now throws a routing exception, so it requires `marko/routing` directly.
- Negotiation choice: use `Request::wantsJson()` (Accept header), matching `AuthMiddleware`, so the two auth middlewares redirect under identical conditions.

## Scope

### In Scope
- `AdminAuthMiddleware` throws `HttpException::unauthorized()` / `HttpException::forbidden()`; delete `unauthorizedResponse()`, `forbiddenResponse()`, `isJsonRequest()`
- Unit tests asserting thrown exceptions, statuses, and that the permission key is not in the message
- Router-level feature test for JSON/HTML 401 and 403 via `ExceptionRenderer`, plus the browser redirect
- `marko/routing` added to admin-auth `require`
- Docs update in `packages/docs-markdown/docs/packages/admin-auth.md` and the affected 401/envelope statements in `packages/docs-markdown/docs/packages/admin-api.md`

### Out of Scope
- `AuthorizationMiddleware` (#263, #268), `PusherAuthController` (#228, done)
- A `WWW-Authenticate` header on admin 401s (#268)

## Success Criteria
- [ ] No hand-built error responses remain in `AdminAuthMiddleware`
- [ ] JSON 403 body is `{"message":"Forbidden."}`; JSON 401 body is `{"message":"Unauthorized."}`
- [ ] Permission key appears only in the exception context, never in the body
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Throw HttpException from AdminAuthMiddleware | - | completed |
| 002 | Router-level rendering test | 001 | completed |
| 003 | Docs update (admin-auth + admin-api) | 001 | completed |

## Architecture Notes
Middleware throws, the router's `ExceptionRenderer` renders. Browser requests that can follow a redirect still get `Response::redirect('{prefix}/login')`.

## Risks & Mitigations
- Behaviour change in JSON body shape (`error` -> `message`): called out in the docs and PR description; pre-1.0, no shim.
