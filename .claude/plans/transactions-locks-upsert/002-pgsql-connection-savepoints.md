# Task 002: PgSqlConnection savepoints and callbacks

**Status**: pending
**Depends on**: [001]
**Retry count**: 0

## Description
In PgSqlConnection, replace the nested-transaction exception with savepoints. Wire TransactionState into transactionLevel(), afterCommit() and afterRollback().

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-pgsql/tests/Connection/PgSqlConnectionTest.php (the SQLite mock PDO supports SAVEPOINT)

## Requirements (Test Descriptions)
- [ ] `it opens a savepoint for a nested beginTransaction`
- [ ] `it releases the savepoint when a nested level commits`
- [ ] `it rolls back to the savepoint when a nested level rolls back`
- [ ] `it reports transactionLevel through nesting and after rollback`
- [ ] `it commits nested transaction() calls together`
- [ ] `it rolls back only the inner work when a nested transaction() failure is caught`
- [ ] `it runs after-commit callbacks after the outermost commit only`
- [ ] `it throws TransactionException when committing or rolling back with no open transaction`
- [ ] `it clears transaction state on reset`
- [ ] `it clears transaction state on disconnect so the next beginTransaction issues BEGIN, not SAVEPOINT`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
