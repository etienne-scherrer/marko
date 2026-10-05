# Task 003: MySqlConnection savepoints and callbacks

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
Mirror task 002 in MySqlConnection to keep the sibling drivers in parity.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/tests/Connection/MySqlConnectionTest.php

## Requirements (Test Descriptions)
- [x] `it opens a savepoint for a nested beginTransaction`
- [x] `it releases the savepoint when a nested level commits`
- [x] `it rolls back to the savepoint when a nested level rolls back`
- [x] `it reports transactionLevel through nesting and after rollback`
- [x] `it commits nested transaction() calls together`
- [x] `it rolls back only the inner work when a nested transaction() failure is caught`
- [x] `it runs after-commit callbacks after the outermost commit only`
- [x] `it throws TransactionException when committing or rolling back with no open transaction`
- [x] `it clears transaction state on reset`
- [x] `it clears transaction state on disconnect so the next beginTransaction issues BEGIN, not SAVEPOINT`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
