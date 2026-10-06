# Task 006: MySqlConnection retry loop and COMMIT translation

**Status**: completed
**Depends on**: 001, 002, 004
**Retry count**: 0

## Description
Same as task 005 for `MySqlConnection`. MySQL rolls back the whole transaction on a deadlock, so `ROLLBACK TO SAVEPOINT` in a nested level fails; the nested level must surface the deadlock (not the "savepoint does not exist" error) so the outermost level can retry.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/tests/Connection/MySqlConnectionSavepointTest.php
- Task 002 already added the `$attempts` parameter and the below-one validation; this task adds the retry loop.
- Follow the shared contract in `_plan.md` Architecture Notes ("Retry contract"); task 005 implements the same contract for PostgreSQL in parallel.
- Translate statement failures with the literal SQL as the `$sql` argument: `'COMMIT'`, `'RELEASE SAVEPOINT marko_sp_N'`, `'ROLLBACK TO SAVEPOINT marko_sp_N'`.
- **OUTERMOST-level gotcha:** after a 1213 deadlock the server has already ended the transaction. pdo_mysql reports `inTransaction()` from the server status flag, so it returns false, and since PHP 8.0 `PDO::rollBack()` then throws `PDOException: There is no active transaction`. Today `rollback()` calls `$this->pdo->rollBack()` at level 1 without a guard, so that raw `PDOException` would replace the `DeadlockException` and nothing would be retried. Guard the level-1 `rollBack()` with `$this->pdo->inTransaction()`, as `commit()`'s failure path already does. The level then closes through `transactionState->rollback()`, and its after-rollback callbacks run because the data really was rolled back.

## Requirements (Test Descriptions)
- [x] `it retries the outermost transaction on a conflict and returns the successful result`
- [x] `it gives up after the given number of attempts and rethrows the last conflict`
- [x] `it does not retry on exceptions that are not transaction conflicts`
- [x] `it does not retry when attempts is one`
- [x] `it discards after-commit callbacks registered in a failed attempt`
- [x] `it never retries a nested transaction by itself`
- [x] `it surfaces the conflict when the savepoint rollback after it fails`
- [x] `it retries when the COMMIT statement reports a conflict`
- [x] `it does not retry when an after-commit callback throws a conflict`
- [x] `it rejects an attempts value below one, even in a nested call, before opening a level`
- [x] `it skips the ROLLBACK when the server already ended the transaction and runs the after-rollback callbacks`
- [x] `it retries an outermost deadlock when PDO reports no active transaction` (fake PDO: `inTransaction()` false, `rollBack()` throws "There is no active transaction")
- [x] `it runs the after-rollback callbacks of each failed attempt`
- [x] `it starts each retry from transaction level zero`
- [x] `it translates a failed COMMIT into a typed exception carrying the COMMIT statement`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Same transaction section as 005 (identical code in both drivers, matching the existing duplication). ScriptedPdo::endTransactionOnServer() mimics a MySQL deadlock (inTransaction() false, ROLLBACK throws, savepoints gone); two extra tests cover the outermost and nested cases.
