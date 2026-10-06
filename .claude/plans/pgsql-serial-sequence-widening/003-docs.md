# Task 003: Docs Update

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update the "Type Changes" section of the PostgreSQL docs page: the sequence now changes type with an auto-increment key, and the manual `ALTER SEQUENCE` step is gone.

## Context
- Related files: `packages/docs-markdown/docs/packages/database-pgsql.md`, `docs/DOCS-STANDARDS.md`

## Requirements (Test Descriptions)
- [x] `it documents that the owned sequence follows an auto-increment key's type change`
- [x] `it documents the error for a sequence not owned by the column`
- [x] `it no longer asks for a manual ALTER SEQUENCE step`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
Added an Auto-Increment Keys subsection under Type Changes in `database-pgsql.md` and dropped the manual ALTER SEQUENCE step; `database.md` Column Changes mentions the DO block.
