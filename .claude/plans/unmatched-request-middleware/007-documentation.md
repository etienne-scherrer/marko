# Task 007: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Document which middleware runs on unmatched requests, lazy session persistence, CSRF behaviour on unmatched requests, and CORS preflight behaviour.

## Context
- Related files: packages/docs-markdown/docs/packages/{routing,session,security,cors,testing}.md, .claude/architecture.md

## Requirements (Test Descriptions)
- [x] routing.md "Unmatched Requests" section lists the opt-in rule and `#[RunsOnUnmatched]`
- [x] session.md documents lazy persistence and the new interface methods
- [x] security.md documents CSRF on unmatched requests
- [x] cors.md documents preflights to unknown paths
- [x] routing.md and session.md warn that session/auth middleware no longer run on unmatched requests, so custom 404/405 error templates must not read the session, auth user, flash, or CSRF token (they would throw `SessionNotStartedException`, turning a 404 into a 500)
- [x] session.md notes that a session is persisted only when resumed from a valid inbound cookie or modified, and that `discard()` closes without writing

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
