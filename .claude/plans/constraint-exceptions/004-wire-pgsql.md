# Task 004: Wire translator into PgSqlConnection and PgSqlStatement

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Catch `PDOException` around prepare/execute in `PgSqlConnection::query()`/`execute()`/`prepare()` and `PgSqlStatement::execute()` and throw the translated exception.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, PgSqlStatement.php
- Touch only the execute call sites (hotspot with #159/#176)
- Add `private readonly PgSqlExceptionTranslator $exceptionTranslator = new PgSqlExceptionTranslator()` as the last constructor parameter on both `PgSqlConnection` (after `$charset`) and `PgSqlStatement`. It must stay optional: `PgSqlConnectionFactory` and many existing tests construct these classes directly.
- `prepare()` passes the connection's translator into `new PgSqlStatement(...)`. The statement reads its SQL from `PDOStatement::$queryString`.
- Catch only `PDOException`. `ConnectionException` (from `ensureConnected()` / `invalidArrayBinding`) must pass through unchanged.
- Unit tests: reuse the sqlite-backed PDO from `PgSqlConnectionTest` (override `createPdo`). sqlite constraint errors are SQLSTATE 23000, so the pgsql translator returns a plain `QueryException`. Assert `QueryException` with the PDOException as previous, or inject a stub translator subclass to assert delegation.

## Requirements (Test Descriptions)
- [x] `it throws a translated exception when query() fails`
- [x] `it throws a translated exception when execute() fails`
- [x] `it throws a translated exception when a prepared statement fails`
- [x] `it lets connection and array-binding exceptions pass through untranslated`

## Acceptance Criteria
- All requirements have passing tests; existing pgsql tests still pass

## Implementation Notes
