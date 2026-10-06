# Task 006: Docs

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Document which primary-key changes the diff generates and which need a hand-written migration, and drop the hand-written `id` column step from the admin-auth upgrade note.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md, admin-auth.md, database-mysql.md, database-pgsql.md
- Follow docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `database.md lists the generated primary-key change (adding key columns to a table without a key) and the refused ones`
- [x] `admin-auth.md no longer tells users to add the id columns by hand on current versions`
- [x] `driver docs no longer claim every primary-key change is refused`
- [x] `database.md says a key column added to a table with rows needs auto-increment or a per-row default (e.g. UUID), and that replacing a key or dropping part of a composite key needs a hand-written migration`
- [x] `database-mysql.md notes that a primary-key flag change on an existing column now throws instead of being skipped (it also fails the production drift check)`

## Acceptance Criteria
- Docs accurate against the implementation

## Implementation Notes
(Left blank - filled in by programmer during implementation)
