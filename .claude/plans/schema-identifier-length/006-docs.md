# Task 006: Documentation

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Document the 63-byte rule: the shortened form of derived names, the previously undocumented `fk_<table>_<column>` name, the error for over-long declared names, and that `ignore_indexes` patterns see the shortened name.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md (Unique Columns on Existing Tables, Indexes, ignore_indexes), database-mysql.md, database-pgsql.md, packages/database/README.md (slim pointer; change only if needed)
- Follow docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `it documents the shortened derived name form in database.md`
- [x] `it documents the fk_<table>_<column> name`
- [x] `it documents the declared index name limit and its exception`
- [x] `it documents the 63-byte rule on the MySQL and PostgreSQL driver pages`
- [x] `it documents the upgrade impact`: a declared `#[Index]` name over 63 bytes (which PostgreSQL silently truncated before) now throws on db:diff/db:migrate. Renaming it makes the next migration drop the truncated index and create the new one. Existing derived names that already fit are unchanged.

## Acceptance Criteria
- Docs follow DOCS-STANDARDS.md

## Implementation Notes
Done.
