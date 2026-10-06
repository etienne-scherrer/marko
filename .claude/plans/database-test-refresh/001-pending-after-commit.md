# Task 001: Run pending after-commit callbacks without committing

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
RefreshDatabase never commits, so after-commit callbacks queued during a test never run. Add a driver hook that runs (and removes) the callbacks queued at every open level as though the outermost transaction committed, without committing.

## Context
- Related files: packages/database/src/Connection/TransactionState.php, new packages/database/src/Connection/PendingAfterCommitInterface.php, PgSqlConnection, MySqlConnection, ReadWriteConnection
- Patterns to follow: TransactionStateTest, driver parity

## Requirements (Test Descriptions)
- [ ] `it runs the after-commit callbacks of every open level in registration order without closing a level`
- [ ] `it removes the callbacks it ran so a later commit does not run them again`
- [ ] `it keeps after-rollback callbacks registered`
- [ ] `it runs pending after-commit callbacks on the pgsql and mysql connections`
- [ ] `it delegates pending after-commit callbacks to the write connection`
- [ ] `it throws a clear exception when the read-write write connection cannot run pending after-commit callbacks`
- [ ] `it does not run a callback twice when an earlier callback throws`
- [ ] `it queues callbacks registered while running instead of running them in the same call`
- [ ] `it does nothing when no transaction is open`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Semantics (from devil's advocate review):
  - Order: outermost level first, registration order within a level (the order an outermost COMMIT would produce).
  - Remove each callback before invoking it (mirrors `commit()` popping before running), so an exception leaves already-run callbacks gone and the rest still queued; the exception propagates.
  - Callbacks registered during the run land on the innermost open level and are not run by the same call.
  - Levels stay open; after-rollback callbacks are untouched.
- `ReadWriteConnection::$write` is typed `ConnectionInterface&TransactionInterface`, so it may not implement `PendingAfterCommitInterface`. ReadWrite always implements the interface, so it must throw a clear exception itself (add a factory method on an existing ReadWrite/database exception) when its write connection does not support it.
