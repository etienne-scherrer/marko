# Plan: Database Concurrency Exceptions and Retries

## Created
2026-10-05

## Status
completed

## Objective
Give deadlocks, serialization failures and lock timeouts their own exception types in `marko/database`, map them in the PostgreSQL and MySQL translators, and add an opt-in retry to `TransactionInterface::transaction()` for the retryable ones.

## Related Issues
Closes #230

## Discovery Notes
- #177 added `PgSqlExceptionTranslator` / `MySqlExceptionTranslator`, which map constraint violations only; every other driver error becomes a plain `QueryException`.
- `PgSqlConnection` and `MySqlConnection` have near-identical `transaction()`/`commit()`/`rollback()` code on top of the shared `TransactionState` (#176). `commit()` lets the raw `PDOException` from `PDO::commit()` escape untranslated, so a PostgreSQL serialization failure detected at COMMIT (write skew under SERIALIZABLE) cannot be caught as a typed exception today. It is translated as part of this plan.
- 27 in-repo classes implement `transaction(callable $callback)` (drivers, `ReadWriteConnection`, `TestDatabase`, many test fakes). Adding a parameter to the interface signature, or adding a new interface method, breaks every implementor equally, so the "additive" `retryingTransaction()` alternative is not additive for implementors. The plan adds `int $attempts = 1` to `transaction()` (as the exit criteria spell it) and labels the PR `breaking`.
- On MySQL a deadlock (1213) rolls back the whole transaction server-side, so `ROLLBACK TO SAVEPOINT` in a nested level fails ("SAVEPOINT does not exist"). The nested level must surface the conflict, not the rollback error, so the outer level can retry.
- The existing `noWait()` integration tests (both drivers) still expect `PDOException`, which is stale since #177. They are only run when `MARKO_TEST_PGSQL_*` / `MARKO_TEST_MYSQL_*` are set (CI does not set them). They are fixed while being edited.
- Integration deadlocks need a second process, since one PHP process can only block on one connection at a time: a small contender script is run with `proc_open()`.

## Scope

### In Scope
- `TransactionConflictException` (with `isRetryable()`), `DeadlockException`, `SerializationFailureException`, `LockTimeoutException` in `marko/database`
- Translator mappings: pgsql `40001`, `40P01`, `55P03`; mysql `1213`, `1205`, `3572`
- Translation of COMMIT / savepoint statement failures in both drivers
- `transaction(callable $callback, int $attempts = 1)` with outermost-only retry on `TransactionConflictException`, in both drivers, `ReadWriteConnection`, `TestDatabase` and all test fakes
- Unit tests, integration tests (pgsql + mysql groups), docs pages and READMEs

### Out of Scope
- HTTP status mapping for the new exceptions (left unmapped by design: an unretried deadlock is a 500)
- Back-off/jitter between attempts (retries run immediately; callers wanting a delay can loop themselves)
- MariaDB 11.6 snapshot-isolation error 1020
- Isolation-level API on `TransactionInterface`

## Success Criteria
- [x] Each listed SQLSTATE / error number maps to the right class in both translators; unrelated codes still give `QueryException`
- [x] Integration: real deadlock -> `DeadlockException`; `noWait()` on a locked row -> `LockTimeoutException`; pgsql SERIALIZABLE conflict -> `SerializationFailureException`
- [x] `transaction($cb, attempts: 3)` retries conflicts, gives up after 3, ignores other exceptions, discards after-commit callbacks of failed attempts, never retries a nested level
- [x] `ReadWriteConnection` delegates `$attempts`
- [x] Docs: "Concurrency errors and retries" section in database.md, codes in database-pgsql.md / database-mysql.md, readwrite signature
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Concurrency exception classes | - | completed |
| 002 | `transaction()` attempts parameter on the interface and every implementor (drivers: signature + validation only; ReadWriteConnection forwarding, absorbed from 007) | 001 | completed |
| 003 | PostgreSQL translator mappings | 001 | completed |
| 004 | MySQL translator mappings | 001 | completed |
| 005 | PgSqlConnection retry loop and COMMIT translation | 001, 002, 003 | completed |
| 006 | MySqlConnection retry loop, COMMIT translation, guarded outermost rollback | 001, 002, 004 | completed |
| 007 | (merged into 002) | 002 | completed |
| 008 | PostgreSQL integration tests | 005 | completed |
| 009 | MySQL integration tests | 006 | completed |
| 010 | Documentation | 001-006, 008, 009 | completed |

## Architecture Notes
- Exceptions follow the #177 pattern: static `fromDriverError(PDOException, string $sql, array $bindings)`, message from `QueryException::redact(firstLine(...))`, never bindings in the message.
- `TransactionConflictException` is an abstract base (only its subclasses are thrown) that callers `catch`; `isRetryable()` returns true. `LockTimeoutException` extends `QueryException` directly (retry is the caller's call).
- Retry happens only when the call is the outermost level (`transactionLevel() === 0` on entry). A nested call ignores `$attempts`; the conflict propagates.
- After-commit callbacks are run after the COMMIT statement succeeded; an exception from a callback never triggers a retry (the data is committed). Drivers split "COMMIT statement" from "run callbacks" internally to make that distinction.
- `$attempts < 1` throws `TransactionException::invalidAttempts()`.

### Retry contract (shared by tasks 005 and 006, documented by 010)
- Validate `$attempts` first, on every call (nested included), before any statement is sent.
- Each failed attempt is rolled back through `rollback()`, so its after-rollback callbacks run once per failed attempt and its after-commit callbacks are discarded.
- A rollback failure that follows a `TransactionConflictException` never masks the conflict. The conflict is rethrown (or retried) and the rollback error is dropped. Before the next attempt starts, the transaction level is back to 0.
- MySQL: after a 1213 deadlock the server has already ended the transaction, `PDO::inTransaction()` returns false and `PDO::rollBack()` throws "There is no active transaction". The level-1 `rollBack()` must be guarded with `inTransaction()` (see task 006).
- Statement failures are translated with the literal statement as `$sql`: `'COMMIT'`, `'RELEASE SAVEPOINT marko_sp_N'`, `'ROLLBACK TO SAVEPOINT marko_sp_N'`.
- Under `TestDatabase`/RefreshDatabase every application `transaction()` call is nested, so `$attempts` never retries there (document this).

## Risks & Mitigations
- Breaking change for third-party `TransactionInterface` implementors: PR labelled `breaking`, upgrade note in docs.
- Flaky concurrency tests: deadlock tests poll the server for the contender's lock wait instead of sleeping, and assert "one of the two sessions got DeadlockException".
