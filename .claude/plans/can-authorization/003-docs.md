# Task 003: Docs Page and README

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Update `packages/docs-markdown/docs/packages/authorization.md` and the package README to describe the now-working global `#[Can]` enforcement.

## Context
- Related files: `packages/docs-markdown/docs/packages/authorization.md`, `packages/authorization/README.md`, `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] Docs state the middleware runs globally once installed
- [x] Docs explain ordering relative to the session
- [x] Docs explain class-level vs method-level `#[Can]`
- [x] Docs explain class-string vs instance checks and why instance checks live in the controller
- [x] README quick example shows `#[Can]` (slim pointer format)

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
