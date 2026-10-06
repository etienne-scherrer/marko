# Task 005: MySQL/MariaDB matcher implementation

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
`MySqlIntrospector` implements `ExpressionDefaultMatcherInterface`: `CREATE TEMPORARY TABLE` with one column of the real `COLUMN_TYPE` and the expression default (formatted as MySqlGenerator emits it), read it back with `SHOW COLUMNS`, `DROP TEMPORARY TABLE`, and compare with the real column's `COLUMN_DEFAULT` (unescaped on MySQL), ignoring wrapping parentheses.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, src/Sql/MySqlGenerator.php, tests/Introspection/MySqlIntrospectorTest.php, tests/Integration/ExpressionDefaultsTest.php

## Requirements (Test Descriptions)
- [x] `it reports a match when the temporary table stores the same default as the column`
- [x] `it unescapes the quotes MySQL escapes in information_schema before comparing`
- [x] `it reports no match when the temporary table stores a different default`
- [x] `it drops the temporary table after probing`
- [x] `it throws a MigrationException naming the column and expression when MySQL rejects the expression`
- [x] `it does not unescape the stored default on MariaDB`
- [x] `it drops a leftover probe table before creating it`
- [x] `it lets a connection failure propagate without reporting a rejected expression`
- [x] integration: `it diffs CONCAT and interval arithmetic expression defaults as empty right after creation`
- [x] integration: `it still diffs a changed expression and modifies the column`
- [x] integration: `it fails at diff time for an expression MySQL rejects`

## Implementation Constraints (from devil's advocate review)
- `matchesStoredDefault(string $table, string $column, Expression $expression): bool` is already declared in `packages/database/src/Introspection/ExpressionDefaultMatcherInterface.php`.
- Real column: query its raw `COLUMN_DEFAULT` and `COLUMN_TYPE` from `information_schema.COLUMNS` (`TABLE_SCHEMA = $this->database`). On MySQL (not MariaDB, see `isMariaDb()`), reverse only `\'` -> `'` and `\\` -> `\`, not `stripslashes()`.
- Expression formatting: `MySqlGenerator::formatExpression()` is private. Mirror its rule in the introspector (leave the CURRENT_TIMESTAMP/LOCALTIMESTAMP/LOCALTIME/NOW(n) family bare, put anything else in parentheses unless it already is). Do not inject the generator.
- Probe table: a reserved name (e.g. `` `marko_default_probe` ``), so it never shadows a real table for the session. Run `DROP TEMPORARY TABLE IF EXISTS` before `CREATE TEMPORARY TABLE` and again in `finally`. Read it back with `SHOW COLUMNS FROM` (key `Default`). Compare both sides with `Expression::unwrap()`.
- Errors: only a `QueryException` from `CREATE TEMPORARY TABLE` becomes `MigrationException::rejectedDefaultExpression($table, $column, $expression->sql, $e->getMessage())`. The server message is the reason, which also covers a missing CREATE TEMPORARY TABLES privilege. A `ConnectionException`, or any other query failure, propagates unchanged.
- Integration tests run `new ExpressionDefaultCanonicalizer($introspector)->canonicalize($entity, $database)` before `new DiffCalculator()->calculate(...)`.

## Acceptance Criteria
- All requirements have passing tests (integration against MySQL 8.4; MariaDB spot-checked)

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
