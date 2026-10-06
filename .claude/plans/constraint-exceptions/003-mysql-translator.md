# Task 003: MySqlExceptionTranslator

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Translate a MySQL/MariaDB `PDOException` by driver error code (`errorInfo[1]`), extracting constraint/table/column from `errorInfo[2]`.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlExceptionTranslator.php (new), packages/database-pgsql/src/Connection/PgSqlExceptionTranslator.php (sibling to mirror if already built; may be in progress in parallel)
- Signature must match the pgsql sibling: `translate(PDOException $exception, string $sql, array $bindings): QueryException` (see task 001 Shared Contract)
- mysql: 1062 unique; 1451/1452 (and legacy 1216/1217) FK; 1048 and 1364 not null; 3819 (MySQL) and 4025 (MariaDB) check
- `table()` = constraint-owning table (FK: the child table in `` (`db`.`child`, CONSTRAINT `fk` ...) ``); when the message omits it, fall back to the target table parsed from the SQL, as the pgsql translator does
- 1062 key formats: `for key 'table.key'` (MySQL 8.0.19+) and `for key 'key'` (5.7/MariaDB). Anchor the parse at the end of the message, because the duplicate value may itself contain `' for key '`
- Test-helper function names must be globally unique (Pest loads all package tests in one process), e.g. `mysqlDriverError()`

## Requirements (Test Descriptions)
- [x] `it translates driver code 1062 into a unique violation with the key name`
- [x] `it reads the table from a MySQL 8 'table.key' name and the key alone from the 5.7 and MariaDB form`
- [x] `it translates driver codes 1451 and 1452 into foreign key violations with constraint and table`
- [x] `it translates legacy driver codes 1216 and 1217 into foreign key violations`
- [x] `it translates driver code 1048 into a not-null violation with the column`
- [x] `it translates driver code 1364 into a not-null violation with the column`
- [x] `it translates driver code 3819 into a check violation with the constraint name`
- [x] `it translates MariaDB driver code 4025 into a check violation with the constraint name`
- [x] `it reads the table from the SQL when the driver message omits it`
- [x] `it falls back to QueryException for any other driver code`
- [x] `it never copies the duplicate value from the driver message`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
