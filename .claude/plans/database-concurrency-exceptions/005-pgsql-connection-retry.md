# Task 005: PgSqlConnection retry loop and COMMIT translation

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Implement `transaction(callable $callback, int $attempts = 1)` in `PgSqlConnection`: the outermost call retries on `TransactionConflictException` up to `$attempts` times; a nested call never retries. Translate a `PDOException` raised by COMMIT / RELEASE SAVEPOINT / ROLLBACK statements so a conflict detected at COMMIT is typed. When rolling back a level after a conflict fails, surface the conflict rather than the rollback error.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-pgsql/tests/Connection/PgSqlConnectionSavepointTest.php (fake PDO patterns)
- After-commit callbacks must run only after the COMMIT statement succeeded, and an exception thrown by one never causes a retry.
- Task 002 already added the `$attempts` parameter and the below-one validation; this task adds the retry loop.
- Follow the shared contract in `_plan.md` Architecture Notes ("Retry contract"); task 006 implements the same contract for MySQL in parallel.
- Translate statement failures with the literal SQL as the `$sql` argument: `'COMMIT'`, `'RELEASE SAVEPOINT marko_sp_N'`, `'ROLLBACK TO SAVEPOINT marko_sp_N'`.
- On PostgreSQL a failed transaction stays open in the aborted state until ROLLBACK (`PDO::inTransaction()` is still true), so the level-1 `rollBack()` succeeds. Still apply the "a rollback failure never masks a conflict" rule from the contract.

## Requirements (Test Descriptions)
- [x] `it retries the outermost transaction on a conflict and returns the successful result`
- [x] `it gives up after the given number of attempts and rethrows the last conflict`
- [x] `it does not retry on exceptions that are not transaction conflicts`
- [x] `it does not retry when attempts is one`
- [x] `it discards after-commit callbacks registered in a failed attempt`
- [x] `it never retries a nested transaction by itself`
- [x] `it retries when the COMMIT statement reports a conflict`
- [x] `it does not retry when an after-commit callback throws a conflict`
- [x] `it rejects an attempts value below one, even in a nested call, before opening a level`
- [x] `it runs the after-rollback callbacks of each failed attempt`
- [x] `it starts each retry from transaction level zero`
- [x] `it translates a failed COMMIT into a typed exception carrying the COMMIT statement`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
transaction() loops over attempts (outermost only), commitStatement() sends COMMIT/RELEASE separately from running after-commit callbacks, rollbackAfterFailure() keeps a conflict over a rollback error. BEGIN/SAVEPOINT/COMMIT/RELEASE/ROLLBACK failures are translated with the statement as sql(). rollback() skips the statement when PDO reports no open transaction. Fake: tests/Fixtures/Retry/ScriptedPdo.php; tests: tests/Connection/PgSqlConnectionRetryTest.php.
