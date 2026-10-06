# Task 004: ReadWriteConnection delegation and nested sticky writes

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
Delegate transactionLevel(), afterCommit() and afterRollback() to the write connection. Keep sticky writes on after a nested transaction() returns.

## Context
- Related files: packages/database-readwrite/src/Connection/ReadWriteConnection.php, packages/database-readwrite/tests/Connection/ReadWriteConnectionTest.php

## Requirements (Test Descriptions)
- [x] `it delegates transactionLevel to the write connection`
- [x] `it delegates afterCommit to the write connection`
- [x] `it delegates afterRollback to the write connection`
- [x] `it keeps reads on the write connection after a nested transaction returns inside an outer transaction`
- [x] `it delegates reset to the write connection when the write connection is resettable`
- [x] `it rolls back every nested level on reset when the write connection is not resettable`

Note: today `reset()` calls `write->rollback()` once. With savepoints that undoes only the innermost level and runs after-rollback callbacks, which leaves the outer transaction open in a long-running worker.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
