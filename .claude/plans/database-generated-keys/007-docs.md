# Task 007: Docs pages and READMEs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document the `generated` opt-in and the `supportsReturning()` contract.

## Context
- Related files: `packages/docs-markdown/docs/packages/database.md` ("String and UUID Primary Keys", Column attribute table, ConnectionInterface API), `database-pgsql.md` ("UUID Primary Keys"), `database-mysql.md`, package READMEs (slim pointers per `docs/DOCS-STANDARDS.md`; only update if they show affected API)
- Stale passages to rewrite in `database.md`: ~line 183 ("`Repository::save()` doesn't read a generated key back, so set the id in PHP") and ~line 1401 ("**PostgreSQL** --- uses `INSERT ... RETURNING id`" for batch inserts; now keyed on `supportsReturning()`). Also note upsert does not read generated keys back.

## Requirements (Test Descriptions)
- [x] `database.md shows generated: true on a uuid key and explains RETURNING read-back and the loud errors`
- [x] `database-pgsql.md UUID Primary Keys shows the generated opt-in`
- [x] `database-mysql.md says generated keys must be set in PHP and MariaDB stays on the MySQL behaviour`
- [x] `database.md documents supportsReturning() on ConnectionInterface`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
