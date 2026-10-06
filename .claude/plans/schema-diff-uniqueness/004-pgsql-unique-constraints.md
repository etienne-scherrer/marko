# Task 004: PostgreSQL unique constraints in introspector and generator

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
The PostgreSQL introspector marks indexes that back a UNIQUE constraint (`Index::$constraint`), and PgSqlGenerator drops
them with `ALTER TABLE ... DROP CONSTRAINT` and restores them in down with `ADD CONSTRAINT ... UNIQUE`.

## Context
- Related files: packages/database-pgsql/src/Introspection/PgSqlIntrospector.php, packages/database-pgsql/src/Sql/PgSqlGenerator.php

## Requirements (Test Descriptions)
- [x] `it marks an index that backs a unique constraint as a constraint`
- [x] `it drops a unique constraint with DROP CONSTRAINT`
- [x] `it restores a dropped unique constraint with ADD CONSTRAINT in down`
- [x] `it adds a unique index for a column that becomes unique and drops it in down`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Do NOT change `SqlGeneratorInterface::generateDropIndex(string $table, string $indexName)`: it receives only the
  name, and the interface is shared with MySqlGenerator. In PgSqlGenerator's `generateTableAlterations()`, route each
  `indexesToDrop` entry through a private helper that emits `ALTER TABLE "t" DROP CONSTRAINT "name"` when
  `$index->constraint` is true and `generateDropIndex()` otherwise. `generateAddIndex()` receives the full `Index`, so
  it can emit `ALTER TABLE "t" ADD CONSTRAINT "name" UNIQUE (...)` for a constraint index (used by the down path).
- Mark constraint-backed indexes with one query joining `pg_constraint` (`contype = 'u'`) on `conindid` to the index
  name; do not issue a query per index.
- Existing PgSqlIntrospector unit tests that compare whole `Index` objects with `toEqual` need the flag set.
