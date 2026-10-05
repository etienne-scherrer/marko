# Task 002: PgSqlExceptionTranslator

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Translate a PostgreSQL `PDOException` into the typed hierarchy by SQLSTATE, extracting constraint, table and column from `errorInfo[2]`.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlExceptionTranslator.php (new)
- pgsql: 23505 unique, 23503 FK, 23502 not null, 23514 check
- Signature: `translate(PDOException $exception, string $sql, array $bindings): QueryException` (task 003 mirrors it)
- `table()` = constraint-owning table (FK: child table, i.e. the trailing `on table "x"` for delete); fall back to the target table parsed from the SQL when the message omits it (23505, pre-PG13 23502)
## Requirements (Test Descriptions)
- [x] `it translates SQLSTATE 23505 into a unique violation with the constraint name`
- [x] `it translates SQLSTATE 23503 into a foreign key violation with constraint and table`
- [x] `it translates SQLSTATE 23502 into a not-null violation with column and table`
- [x] `it translates SQLSTATE 23514 into a check violation with the constraint name`
- [x] `it falls back to QueryException for any other SQLSTATE`
- [x] `it reads the SQLSTATE from the exception code when errorInfo is missing`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
