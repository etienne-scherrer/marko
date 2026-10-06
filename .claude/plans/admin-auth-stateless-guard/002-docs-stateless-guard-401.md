# Task 002: Document the stateless-guard 401 in admin-auth and admin-api docs

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update the docs pages so they say a guest on a stateless guard always gets a 401 (with the guard's `WWW-Authenticate` challenge), and only a browser request on a stateful guard is redirected to the admin login.

## Context
- Related files: `packages/docs-markdown/docs/packages/admin-auth.md` (behaviour list under "Protecting Admin Routes"; `AdminAuthMiddleware` API reference note), `packages/docs-markdown/docs/packages/admin-api.md` (Available Endpoints paragraph, line ~20), `packages/docs-markdown/docs/tutorials/build-an-admin-panel.md` (line ~510, "unauthenticated users are redirected to `/admin/login`")
- Patterns to follow: `docs/DOCS-STANDARDS.md`; wording in `packages/docs-markdown/docs/packages/authentication.md` for `AuthMiddleware`

## Requirements (Test Descriptions)
- [x] admin-auth.md behaviour list says a redirect only happens on a stateful guard for a non-JSON request
- [x] admin-auth.md behaviour list says a stateless guard always gets a 401 with its challenge
- [x] admin-auth.md API reference note matches the new behaviour
- [x] admin-api.md Available Endpoints paragraph says stateless guards always get a 401. Rewrite the existing "any other unauthenticated request is redirected" sentence, don't just append to it, and limit the "Send `Accept: application/json` ... rather than a redirect" advice to the session (stateful) guard, so the paragraph doesn't contradict itself
- [x] build-an-admin-panel.md step 1 of the `AdminAuthMiddleware` list limits the redirect to browser requests on the session guard (a JSON request or a stateless guard gets a 401)

## Acceptance Criteria
- Docs match the implemented behaviour
- Docs follow DOCS-STANDARDS

## Implementation Notes
Rewrote the admin-auth.md behaviour list and API note, the admin-api.md Available Endpoints paragraph, and step 1 in tutorials/build-an-admin-panel.md. Also updated the Stateless Guards paragraph in authentication.md to name AdminAuthMiddleware alongside AuthMiddleware. READMEs carry no behaviour detail and needed no change.
