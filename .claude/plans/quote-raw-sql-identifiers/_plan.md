# Plan: Quote Raw SQL Identifiers

## Created
2026-10-06

## Status
completed

## Objective
Route every identifier in hand-built SQL outside the database driver packages through `ConnectionInterface::quoteIdentifier()`, fix reserved-word and mixed-case columns in `DatabaseSearchDriver`, and add a guard test that fails on any new hard-coded identifier quote character in `packages/*/src`.

## Related Issues
Closes #338

## Discovery Notes
- #331 added `ConnectionInterface::quoteIdentifier()`; Repository, DataMigration and DatabaseTestHelper already use it.
- `DatabaseSearchDriver` validates identifiers with `/^[a-zA-Z_][a-zA-Z0-9_]*$/` but interpolates them bare, so `group`/`order`/`key` fail and a PostgreSQL `displayName` column folds to lower case.
- `TruncateDatabase` hard-codes `"` (pgsql) and `` ` `` (mysql).
- `DatabaseQueue` interpolates its configurable `$table` bare in seven raw statements; `reserveNext()` already goes through the query builder.
- Fixed-name sites: `failed_jobs`, `notifications` (two packages), `sessions`, admin-auth pivots/joins.
- `marko/search` uses `ConnectionInterface` but does not require `marko/database`; fixed while adding the integration tests.
- #341 (admin-auth case handling) and #347 (syncRoles transaction) touch the same admin-auth files, so admin-auth edits stay limited to the table names in the SQL strings.

## Scope

### In Scope
- Quote table and every column name in `DatabaseSearchDriver` (keep the identifier regex, keep the sort-direction allowlist)
- Real-database search tests on PostgreSQL, MySQL 8.4 and MariaDB with `group`/`order`/`key` columns and (PostgreSQL) a mixed-case column, tables built from an entity through `SchemaBuilder`
- `TruncateDatabase` builds both branches with `quoteIdentifier()`
- `DatabaseQueue` quotes `$table` in every raw statement
- Fixed-name sites quote their table names through the connection (the issue's recommendation)
- Guard test over `packages/*/src` (excluding database-mysql and database-pgsql)
- Docs: search.md, database.md, testing docs

### Out of Scope
- Quoting fixed, non-reserved column names in fixed-name sites (the tables are what a deployment could plausibly rename or collide on; columns are owned by the class)
- Reading `notifications`/`sessions` table names from entity metadata

### Added during implementation
- `marko/database` `MigrationRepository` (fixed `migrations` table, missed by the issue's survey) quotes its table too, so the guard and the docs' claim cover every raw site outside the drivers.
- The guard flags a statement as SQL only when a literal starts with a statement keyword or contains upper-case FROM/JOIN/INTO/WHERE; a keyword anywhere matched exception prose ("Validating SELECT column alias").

## Success Criteria
- [ ] Every exit criterion in #338 met
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Quote identifiers in DatabaseSearchDriver | - | completed |
| 002 | Real-database search tests (PostgreSQL, MySQL, MariaDB) | 001 | completed |
| 003 | TruncateDatabase quotes through the connection | - | completed |
| 004 | DatabaseQueue and DatabaseFailedJobRepository quote table names | - | completed |
| 005 | Notification and session fixed-name sites | - | completed |
| 006 | admin-auth pivot/join table names | - | completed |
| 007 | Guard test against hard-coded identifier quoting | 001, 003, 004, 005, 006 | completed |
| 008 | Docs | 001-007 | completed |

## Architecture Notes
- Quote once per statement build; bound parameters stay bound; result rows are read by bare column name.
- The guard test lives in the monorepo suite (`tests/`) because each package is split into its own repository, following `ConfigEnvReadsTest`/`Psr7ContainmentTest`.

## Risks & Mitigations
- Merge conflicts with #341/#347 in admin-auth: only table names inside SQL literals change.
- Guard test false positives (e.g. FTS phrase quoting, CSV/JSON code): the detector only flags string literals in statements that contain SQL keywords. Keywords match case-sensitively as uppercase whole words, and interpolated/heredoc string parts are scanned too (see task 007 detector rules).
- Test doubles quote with `"` regardless of driver: the testing `RecordingConnection` fixture must become driver-aware (task 003), and exact-SQL assertions in package unit tests get updated (tasks 004-006).
- Double quoting: `DatabaseQueue::reserveNext()` hands `$table` to the query builder, which quotes it itself, so it must keep the raw name (task 004).
