# Task 002: Document lazy #[Can] enforcement

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
State in the authorization docs page that `#[Can]` enforcement costs nothing on routes without the attribute, and what is required when it is used. Update the API reference constructor signature.

## Context
- Related files: packages/docs-markdown/docs/packages/authorization.md, packages/authorization/README.md

## Requirements (Test Descriptions)
- [x] `docs state AuthorizationMiddleware never resolves the gate, auth manager, guard or authorization config for routes without Can (including unmatched/404 requests)`
- [x] `docs list what a Can route requires (authentication guard, user provider, session for session guards)`
- [x] `docs note that auth misconfiguration now surfaces on the first Can request rather than on every request`
- [x] `docs API reference shows the lazy factory constructor`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md
- README remains a slim pointer

## Implementation Notes
Scope the "costs nothing" claim to `AuthorizationMiddleware`. Do not claim the app skips sessions: `marko/session-file` / `marko/session-database` register `SessionMiddleware` globally, and it still runs on every request whether or not `#[Can]` is present. Edit the existing bullets around line 139-147 ("No `#[Can]`: the request passes through untouched") and the API reference block around line 314-324 (constructor currently shown as `GateInterface $gate, GuardInterface $guard`).

Done: added "Cost on Routes Without `#[Can]`" subsection and updated the API reference constructor in `packages/docs-markdown/docs/packages/authorization.md`. README left as-is (slim pointer, still accurate).
