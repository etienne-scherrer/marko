# Task 003: MySqlConnection savepoints and callbacks

**Status**: pending
**Depends on**: [001]
**Retry count**: 0

## Description
Mirror task 002 in MySqlConnection to keep the sibling drivers in parity.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/tests/Connection/MySqlConnectionTest.php

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
