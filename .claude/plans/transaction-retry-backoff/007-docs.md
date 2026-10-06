# Task 007: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Document the backoff in database.md "Retrying a Transaction", list MariaDB 1020 under SerializationFailureException in both tables, mention `innodb_snapshot_isolation` in database-mysql.md, and add FakeSleeper to testing.md.

## Requirements (Test Descriptions)
- [x] `database.md documents the default, int and closure backoff`
- [x] `database.md lists 1020 under SerializationFailureException`
- [x] `database-mysql.md mentions innodb_snapshot_isolation`
- [x] `testing.md documents FakeSleeper`
- [x] `testing.md's existing transaction() implementation example uses the new signature (int|Closure|null $backoff = null)`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
database.md: Backoff subsection, 1020 in both tables, upgrade note. database-mysql.md: innodb_snapshot_isolation, translator and factory notes. database-pgsql.md and database-readwrite.md: signatures. testing.md and the testing README: FakeSleeper. testing.md had no old-signature transaction() implementation example.
