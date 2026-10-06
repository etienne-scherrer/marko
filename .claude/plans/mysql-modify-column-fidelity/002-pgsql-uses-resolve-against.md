# Task 002: PgSqlGenerator uses resolveAgainst()

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Replace the private `targetColumn()` in `PgSqlGenerator` with `Column::resolveAgainst()`, with no behaviour change on PostgreSQL.

## Context
- Related files: packages/database-pgsql/src/Sql/PgSqlGenerator.php, packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php

## Requirements (Test Descriptions)
- [x] existing PgSqlGenerator tests pass unchanged
- [x] `it ignores native metadata on the previous column`

## Acceptance Criteria
- No PostgreSQL behaviour change

## Implementation Notes
