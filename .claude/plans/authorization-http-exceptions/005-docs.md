# Task 005: Docs and READMEs

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Document the 401/403 behaviour and how to customise rendering.

## Context
- Related files: packages/docs-markdown/docs/packages/authorization.md, packages/docs-markdown/docs/packages/authentication.md, packages/docs-markdown/docs/packages/routing.md, packages/docs-markdown/docs/packages/admin-auth.md, packages/docs-markdown/docs/packages/roadrunner-state-leaks.md, packages/authorization/README.md, packages/authentication/README.md
- Known stale spots: authorization.md:143 (old `{"error":...}` bodies) and :299-318 (API reference, `missingPolicy`); authentication.md:418 (`{"error": "Unauthorized"}`); admin-auth.md:116 (`new AuthorizationException('Cannot cancel orders')` with no `use` statement: add `use Marko\Authorization\Exceptions\AuthorizationException;` and make sure it matches the new constructor); roadrunner-state-leaks.md:49 (`PolicyRegistry` duplicate registration now throws `PolicyException`)

## Requirements (Test Descriptions)
- [x] `authorization.md describes 401/403 rendering and customising via an ExceptionRenderer Preference`
- [x] `authorization.md API reference shows AuthorizationException implementing HttpExceptionInterface and PolicyException`
- [x] `authentication.md describes AuthMiddleware redirect vs 401 via the renderer (token guard always 401)`
- [x] `no doc still shows {"error":"Unauthorized"} / {"error":"Forbidden"} for AuthMiddleware or #[Can], or says PolicyRegistry throws AuthorizationException`
- [x] `routing.md error-mapping table lists AuthorizationException → 403`

## Acceptance Criteria
- Docs match code; READMEs stay slim pointers

## Implementation Notes
