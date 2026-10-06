# Task 004: Docblock and docs

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make the docs say exactly what `insertBatch()` relies on: positional `RETURNING` matching (observed on PostgreSQL and MariaDB 10.5+, covered by CI integration tests), the MySQL `auto_increment_increment` handling, the precise `innodb_autoinc_lock_mode` condition, and `save()` inside `transaction()` for a per-row guarantee.

## Context
- Related files: `packages/database/src/Repository/RepositoryInterface.php`, `packages/docs-markdown/docs/packages/database.md`, `packages/docs-markdown/docs/packages/database-mysql.md`
- Patterns to follow: `docs/DOCS-STANDARDS.md`
- Also update the `Repository::insertBatch()` implementation docblock (`packages/database/src/Repository/Repository.php` ~line 304) if it describes key assignment.

## Facts to state (from devil's advocate review)
- A multi-row `INSERT ... VALUES` is an InnoDB "simple insert": it reserves all of its auto-increment values at once. They are consecutive (stepped by `auto_increment_increment`) under `innodb_autoinc_lock_mode` 0 and 1. Under 2 (the MySQL 8.0+ default) they are consecutive unless a concurrent "bulk insert" (`INSERT ... SELECT`, `LOAD DATA`) on the same table interleaves.
- The MySQL path reads `@@auto_increment_increment` per batch, before the INSERT, and assigns `first id + offset * step`. It throws `BatchInsertException` when the setting can't be read as a positive integer.
- A batch whose entities all carry explicit auto-increment ids keeps those ids.
- `RETURNING` keys are matched to entities by row position: the order PostgreSQL and MariaDB 10.5+ return rows in, covered by CI integration tests with a step of 5. For a per-row guarantee, call `save()` in a loop inside `transaction()`.

## Requirements (Test Descriptions)
- [x] `RepositoryInterface::insertBatch()` docblock states RETURNING row-order matching and the MySQL conditions; stale "PostgreSQL uses RETURNING" removed
- [x] `database.md` "ID assignment after batch insert" states RETURNING order reliance and the `save()` inside `transaction()` alternative
- [x] `database.md` generated keys section no longer over-promises
- [x] `database-mysql.md` states the `auto_increment_increment` handling and the `innodb_autoinc_lock_mode` condition precisely

## Acceptance Criteria
- Docs match the code
- Follows DOCS-STANDARDS

## Implementation Notes
Docblocks (interface and Repository), database.md and database-mysql.md updated; no code changed.
