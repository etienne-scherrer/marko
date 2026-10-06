# Task 004: PgSql generator + introspector partial indexes

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Emit `CREATE INDEX ... WHERE <predicate>` in `PgSqlGenerator` and read the predicate back in `PgSqlIntrospector::getIndexes()` so a partial index round-trips.

## Context
- Related files: packages/database-pgsql/src/Sql/PgSqlGenerator.php, packages/database-pgsql/src/Introspection/PgSqlIntrospector.php

## Requirements (Test Descriptions)
- [x] `it generates a partial index with a WHERE clause`
- [x] `it recreates a dropped partial index with its WHERE clause in the down migration`
- [x] `it reads the WHERE predicate of a partial index`
- [x] `it parses columns of a partial index without the predicate`
- [x] `it produces an empty diff when the partial index already exists`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
