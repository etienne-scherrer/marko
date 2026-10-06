# Plan: AdminAuthMiddleware Never Redirects on a Stateless Guard

## Created
2026-10-06

## Status
completed

## Objective
Make `AdminAuthMiddleware` answer every guest on a stateless guard (`StatelessGuardInterface`, e.g. the token guard) with a 401 `UnauthenticatedException` carrying the guard's `WWW-Authenticate` challenge, instead of redirecting browser-style requests to `{prefix}/login`, matching `AuthMiddleware`.

## Related Issues
Closes #303

## Discovery Notes
- `AuthMiddleware` (`packages/authentication/src/Middleware/AuthMiddleware.php`) only redirects when the guard is not a `StatelessGuardInterface`, a redirect target is set, and the request doesn't want JSON.
- `AdminAuthMiddleware` (`packages/admin-auth/src/Middleware/AdminAuthMiddleware.php`) only checks `wantsJson()`, so a stateless guest without a JSON `Accept` gets a 302.
- The unit test file already has a `StatelessAdminGuard` fake (FakeGuard + StatelessGuardInterface with a `Bearer` challenge) and a `captureHttpException()` helper.
- Docs to update: `packages/docs-markdown/docs/packages/admin-auth.md` (behaviour list under "Protecting Admin Routes" and the `AdminAuthMiddleware` API note), `packages/docs-markdown/docs/packages/admin-api.md` (Available Endpoints paragraph). READMEs carry no behaviour detail.
- Shared-rule extraction (optional in the ticket) is skipped: the two middlewares live in different packages, so sharing the rule would mean a new public API on `marko/authentication`, which the ticket rules out. The condition is one `instanceof` plus `wantsJson()`.

## Scope

### In Scope
- `AdminAuthMiddleware::handle()` redirect condition and class docblock
- Unit tests for the stateless browser case (and the stateless no-Accept variant)
- Docs pages admin-auth.md and admin-api.md, plus the redirect claim in tutorials/build-an-admin-panel.md

### Out of Scope
- Session-guard redirect behaviour and the 403 path (unchanged)
- A shared "should redirect" helper / new public API
- Feature test through the real router

## Success Criteria
- [x] A guest browser request on a stateless guard gets a 401 `UnauthenticatedException` with `WWW-Authenticate: Bearer`, not a redirect
- [x] A guest browser request on a stateful guard is still redirected to `{prefix}/login`; a guest JSON request still gets a 401 on both guard kinds
- [x] Docs say stateless guards always get a 401
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Stop redirecting guests on a stateless guard in AdminAuthMiddleware | - | completed |
| 002 | Document the stateless-guard 401 in admin-auth, admin-api and admin-panel tutorial docs | 001 | completed |

## Architecture Notes
Mirror `AuthMiddleware`'s condition exactly: `!$guard instanceof StatelessGuardInterface && !$request->wantsJson()` redirects; everything else throws `UnauthenticatedException::forGuard($guard)`.

## Risks & Mitigations
- Behaviour change for apps whose default guard is stateless and that relied on the redirect: intended by the issue; documented in the docs pages and PR body.
