# Task 006: Docs

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Update database-mysql.md ("MySQL vs MariaDB", "Locking and upsert on MySQL and MariaDB") and the lock table in database.md to show per-server SQL; document MySqlServer briefly; keep the README slim.

## Context
- Related files: packages/docs-markdown/docs/packages/database-mysql.md, database.md, packages/database-mysql/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `it documents LOCK IN SHARE MODE NOWAIT and SKIP LOCKED for MariaDB`
- [x] `it documents server detection with MySqlServer`
- [x] `it removes the statement that MariaDB rejects shared-lock modifiers`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
