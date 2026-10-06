# Task 006: Documentation

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Update the session docs to describe strict ids: a session is resumed only when the store knows it, unknown/expired ids are discarded and their cookie expired, unchanged resumed sessions refresh their expiry through `updateTimestamp()`, and custom handlers must implement `validateId()` and `updateTimestamp()`.

## Context
- Related files: packages/docs-markdown/docs/packages/session.md, session-file.md, session-database.md; package READMEs (slim pointers, likely unchanged)
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `it documents that only a session the store knows is resumed`
- [x] `it documents the two handler methods custom handlers must implement`

## Acceptance Criteria
- Docs accurate for both drivers
- Existing docs tests pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
