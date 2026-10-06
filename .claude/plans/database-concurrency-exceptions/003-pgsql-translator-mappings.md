# Task 003: PostgreSQL translator mappings

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Map SQLSTATE `40P01` to `DeadlockException`, `40001` to `SerializationFailureException`, and `55P03` to `LockTimeoutException` in `PgSqlExceptionTranslator`; update the doc comment.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlExceptionTranslator.php, packages/database-pgsql/tests/Connection/PgSqlExceptionTranslatorTest.php

## Requirements (Test Descriptions)
- [x] `it translates SQLSTATE 40P01 into DeadlockException`
- [x] `it translates SQLSTATE 40001 into SerializationFailureException`
- [x] `it translates SQLSTATE 55P03 into LockTimeoutException`
- [x] `it still translates an unrelated SQLSTATE into a plain QueryException`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
40P01, 40001, 55P03 mapped; fallback test now also asserts the result is neither a conflict nor a lock timeout.
