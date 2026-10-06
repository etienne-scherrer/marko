# Plan: Admin API Error Shape

## Created
2026-10-06

## Status
completed

## Objective
Give every error from `/admin/api/v1/*` one body shape, `{"message": ...}`, by having admin-api controllers throw `HttpException` (rendered by routing's `ExceptionRenderer`) and removing the `ApiResponse` error helpers (option A of #292).

## Related Issues
Closes #292
Relates to #303

## Discovery Notes
- `ApiResponse::error()`/`notFound()`/`forbidden()`/`unauthorized()` produce `{"errors":[{"message":...}]}`; only `SectionController::show()` (two 404s) and `MeController::me()` (401) use them.
- `AdminAuthMiddleware` already throws `UnauthenticatedException` (401) and `HttpException` (403) rendered as `{"message":...}` by `ExceptionRenderer`.
- `MeController`'s 401 branch is reachable only for an authenticated user that is not an `AdminUserInterface` (the middleware already rejects guests). The middleware answers that same situation with a 403 `Forbidden.`, so the controller throws a 403 with a log-only context to match.
- `packages/admin-auth/tests/Feature/AdminAuthRouterTest.php` shows how to build a real `Router` with `AdminAuthMiddleware` for router-level tests.
- Docs to update: `packages/docs-markdown/docs/packages/admin-api.md`, `packages/docs-markdown/docs/tutorials/build-an-admin-panel.md` (ApiResponse table). README quick example uses only `paginated()`.

## Scope

### In Scope
- `SectionController::show()` throws `HttpException::notFound("Section '$id' not found")`
- `MeController::me()` throws a 403 `HttpException` for a non-admin user
- Remove `ApiResponse::error()`, `notFound()`, `forbidden()`, `unauthorized()` (pre-1.0, breaking)
- Router-level feature test for unknown section (404), guest JSON (401), forbidden (403)
- Docs page, tutorial table

### Out of Scope
- Stateless-guard redirect behaviour of `AdminAuthMiddleware` (#303)
- Any admin-api specific `ExceptionRenderer` preference

## Success Criteria
- [x] Controllers throw `HttpException`; no `ApiResponse` error helpers remain
- [x] Router-level test proves one `{"message": ...}` shape for 404/401/403
- [x] Docs describe one error shape and link to the routing errors section
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Controllers throw HttpException, remove ApiResponse error helpers | - | completed |
| 002 | Router-level error shape test | 001 | completed |
| 003 | Docs and tutorial update | 001 | completed |

## Architecture Notes
Errors flow through the single framework pipeline (`HttpException` -> `ExceptionRenderer`). Apps wanting a different JSON error shape override `ExceptionRenderer::renderJson()` via `#[Preference]`.

## Risks & Mitigations
- Breaking change for clients reading `errors[0].message`: label PR `breaking`, add "Behaviour change" section.
