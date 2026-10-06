# Task 012: Documentation

**Status**: completed
**Depends on**: [001, 002, 003, 004, 005, 006, 007, 008, 009, 010, 011]
**Retry count**: 0

## Description
Document nested transactions, after-commit callbacks, locking and upsert in database.md. Document where the drivers differ in database-pgsql.md, database-mysql.md and database-readwrite.md.

## Context
- Related files: packages/docs-markdown/docs/packages/*.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `it documents nested transactions, afterCommit/afterRollback, locking and upsert`
- [x] `it documents driver differences`:
  - MySQL ignores `uniqueBy` (any unique key triggers the update)
  - PostgreSQL needs a matching unique constraint and rejects duplicate conflict keys within one batch
  - affected-row counts differ by driver
  - `sharedLock()` with a modifier is MySQL 8 only, and `SKIP LOCKED` needs MariaDB 10.6+
  - MySQL `VALUES()` is deprecated
  - locks do not apply to eager-loaded relations
  - after-commit callbacks never fire under `DatabaseTestHelper`'s wrapping transaction

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
