# Task 004: PostgreSQL integration test: modified default converges

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Against a real PostgreSQL server (skipped unless `MARKO_TEST_PGSQL_HOST` is set): create a table, diff an entity schema whose default and nullability differ, apply the generated up SQL, re-introspect and diff again — the second diff is empty. Applying the down SQL restores the original column.

## Context
- Related files: packages/database-pgsql/tests/Integration/*, PgSqlIntrospector, DiffCalculator
- Create a NEW file `packages/database-pgsql/tests/Integration/ModifyColumnMigrationTest.php`. Do not edit the existing Integration tests or fixtures: issue #230 is changing them in parallel. Reuse `SharedConnectionContainer::config()` read-only.
- Pest files declare namespaced global functions, so use unique helper names (e.g. `pgsqlModifyColumnConfig()`) and a unique skip-reason constant name.
- Use a unique table name (e.g. `modify_column_items`). Drop it in `beforeEach` (`DROP TABLE IF EXISTS`) and `afterEach`.
- Build the entity-side `Table` by hand with explicit `type: 'varchar'` and explicit `length`, so the diff's length/type tolerances do not add noise. Get the database side from `PgSqlIntrospector::getTable()`.

## Requirements (Test Descriptions)
- [x] `it yields an empty diff after applying a generated default change`
- [x] `it yields an empty diff after applying a generated nullability change`
- [x] `it keeps the database default when the entity declares none and only nullability changes`
- [x] `it restores the original column when the down migration runs`

## Acceptance Criteria
- Tests pass against a real PostgreSQL server and skip cleanly otherwise

## Implementation Notes
(Left blank - filled in by programmer during implementation)
