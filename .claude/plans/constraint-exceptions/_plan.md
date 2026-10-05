# Plan: Constraint-Violation Exceptions

## Created
2026-10-05

## Status
completed

## Objective
Translate driver `PDOException`s raised while running SQL into a typed, driver-agnostic exception hierarchy in `marko/database` (`QueryException` and the `*ConstraintViolationException` family), so application code can handle duplicate keys and FK violations without knowing the driver, and the HTTP layer renders unique/FK violations as 409 instead of 500.

## Related Issues
Closes #177

## Discovery Notes
- `PgSqlConnection` / `MySqlConnection` `query()`/`execute()` and `*Statement::execute()` call PDO with no `catch`; the raw `PDOException` escapes.
- `HttpExceptionInterface` (#169) lives in `marko/core` (`Marko\Core\Exceptions\HttpExceptionInterface`), so `marko/database` can implement it with no new dependency. `EntityNotFoundException` already does this (404) and is the pattern to follow.
- `Repository::insertBatch()` does not wrap in `BatchInsertException`; it rolls back and rethrows the caught `Throwable`. Once drivers translate, the rethrown exception *is* the typed exception with the `PDOException` as `previous`, which is what step 4 asks for. Wrapping it in `BatchInsertException` would hide the type from `catch (UniqueConstraintViolationException)`, so it stays a rethrow; a test pins the behaviour.
- PostgreSQL error text carries row data in its `DETAIL:` line (`Key (email)=(a@b.c) already exists`, `Failing row contains (...)`) and MySQL's 1062 message includes the duplicate value. Messages are therefore built from parsed parts (constraint, table, column), never by copying the driver text; the fallback `QueryException` uses the first line of the driver message with string bindings redacted.
- `ReadWriteConnection` catches `MarkoException` from replicas, so `QueryException` (a `MarkoException`) keeps the replica fallback behaviour.
- Real-driver coverage: the per-driver `tests/Integration` pattern (env-gated `MARKO_TEST_PGSQL_HOST` / `MARKO_TEST_MYSQL_HOST`) plus the #187 app suite (`KnownGapsTest` has a #177 todo to flip).

## Scope

### In Scope
- `QueryException`, `ConstraintViolationException`, `UniqueConstraintViolationException`, `ForeignKeyConstraintViolationException`, `NotNullConstraintViolationException`, `CheckConstraintViolationException` in `marko/database`
- `PgSqlExceptionTranslator` and `MySqlExceptionTranslator` in the driver packages, wired into `query()`, `execute()` and `*Statement::execute()`
- Unique and FK violations implement `HttpExceptionInterface` (409, generic body)
- Unit, real-driver and HTTP rendering tests; flip the #177 known-gap todo
- Docs page section with a "handle a duplicate email" example

### Out of Scope
- Mapping other SQLSTATE classes (deadlocks, serialization failures, lock timeouts) to their own types
- Translating PDO errors raised by `connect()` (already `ConnectionException`), `beginTransaction()`/`commit()`
- Changing `ReadWriteConnection`

## Success Criteria
- [x] Both translators map every listed SQLSTATE/driver code, extract constraint names, and fall back to `QueryException`
- [x] Real pgsql/mysql: duplicate unique → `UniqueConstraintViolationException` with the constraint name; deleting a referenced row → `ForeignKeyConstraintViolationException`; `getPrevious()` is the `PDOException`
- [x] Bindings never appear unredacted in exception messages
- [x] Uncaught `UniqueConstraintViolationException` in a controller renders 409
- [x] Docs page documents the hierarchy with a "handle a duplicate email" example
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Exception hierarchy in marko/database | - | completed |
| 002 | PgSqlExceptionTranslator | 001 | completed |
| 003 | MySqlExceptionTranslator | 001 | completed |
| 004 | Wire translator into PgSqlConnection/PgSqlStatement (query/execute/prepare) | 002 | completed |
| 005 | Wire translator into MySqlConnection/MySqlStatement (query/execute/prepare) | 003 | completed |
| 006 | Real-driver integration tests (pgsql, mysql) | 004, 005 | completed |
| 007 | HTTP 409 rendering + app integration suite | 001, 004 | completed |
| 008 | Docs page and README | 001-007 | completed |

## Architecture Notes
- Translation lives in the drivers (per-driver `*ExceptionTranslator` class, sibling naming), not the repository, so raw `query()`/`execute()`, the query builder and prepared statements all get it. Connection and statement only change at the `execute()` call sites (hotspot with #159/#176).
- Translator is a constructor parameter with a `new` default so module factories need no change and a Preference can replace it.
- Exceptions use `MarkoException` `message`/`context`/`suggestion`; `sql()`, `bindings()`, `sqlState()`, `constraintName()`, `table()`, `column()` accessors. Full contract in task 001 "Shared Contract".
- Both translators expose `translate(PDOException $e, string $sql, array $bindings): QueryException`; `table()` is the constraint-owning table (FK: child table) on both drivers, falling back to the SQL target table when the driver message omits it.
- Translator is the last, optional constructor parameter on both the connection and the statement; only `PDOException` is caught.
- Behaviour change: `catch (PDOException)` around connection/repository calls stops matching. Documented as an upgrade note (task 008).
- PostgreSQL aborts the surrounding transaction after a violation (25P02 afterwards); tests and docs must account for it.

## Risks & Mitigations
- Driver message format differences across server versions (MySQL 5.7 vs 8 `for key 'table.key'`): regexes accept both forms; unknown formats leave the name `null` rather than failing.
- Concurrent edits in `packages/database*` by #170/#176: keep diffs minimal and isolated.
- Integration test collisions: new real-driver test files must not redeclare the namespaced helpers in `SharedConnectionTransactionTest.php` and must use dedicated tables (task 006). The app-suite flip must not add a fixture migration (`ServicesTest` pins 5 migrations) and must tag tests `->issue(177)` (task 007).
- MySQL is not in compose/CI; MySQL real-driver tests only run locally.
