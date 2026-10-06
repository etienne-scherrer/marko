# Task 005: Wire translator into MySqlConnection and MySqlStatement

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Mirror task 004 for the MySQL driver: translate `PDOException` in `MySqlConnection::query()`/`execute()`/`prepare()` and `MySqlStatement::execute()`.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, MySqlStatement.php
- Same rules as task 004:
  - The translator is the last, optional constructor parameter (`= new MySqlExceptionTranslator()`) on the connection and the statement.
  - `prepare()` hands the translator to the statement, and the statement reads the SQL from `PDOStatement::$queryString`.
  - Catch only `PDOException`.
- `prepare()` can itself throw when emulated prepares are off, so wrap it too.
- Unit tests: sqlite-backed PDO as in `MySqlConnectionTest`. sqlite driver code 19 is not a MySQL code, so expect a plain `QueryException`, or use a stub translator.

## Requirements (Test Descriptions)
- [x] `it throws a translated exception when query() fails`
- [x] `it throws a translated exception when execute() fails`
- [x] `it throws a translated exception when a prepared statement fails`
- [x] `it lets connection and array-binding exceptions pass through untranslated`

## Acceptance Criteria
- All requirements have passing tests; existing mysql tests still pass

## Implementation Notes
