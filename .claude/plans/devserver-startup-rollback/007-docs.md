# Task 007: Docs page

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Document rollback on failure, IPv6 host syntax, `dev:open` host handling, the detached stop behavior and the new exceptions in `packages/docs-markdown/docs/packages/devserver.md`. The README is already a slim pointer.

## Context
- Related files: packages/docs-markdown/docs/packages/devserver.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] Docs describe rollback on failure in both modes
- [x] Docs describe `--host` IPv6 syntax (`::1`, `[::1]`, `::`)
- [x] Docs list `invalidHost`, `serverExited` and `rollbackFailed`

## Acceptance Criteria
- Docs match behavior

## Implementation Notes
