# Task 001: TransactionState and TransactionInterface additions

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a driver-agnostic TransactionState class that tracks transaction depth and after-commit/after-rollback callbacks. Add transactionLevel(), afterCommit() and afterRollback() to TransactionInterface, and remove TransactionException::nestedTransactionNotSupported().

## Context
- Related files: packages/database/src/Connection/TransactionInterface.php, packages/database/src/Exceptions/TransactionException.php, new packages/database/src/Connection/TransactionState.php
- Patterns: plain mutable class, no traits

## Requirements (Test Descriptions)
- [x] `it starts at level zero`
- [x] `it increments the level on begin and decrements on commit and rollback`
- [x] `it runs an after-commit callback immediately at level zero`
- [x] `it runs after-commit callbacks only after the outermost commit`
- [x] `it merges inner after-commit callbacks into the parent level on inner commit`
- [x] `it discards after-commit callbacks of a rolled-back level`
- [x] `it runs after-rollback callbacks of a rolled-back level`
- [x] `it ignores after-rollback callbacks registered at level zero`
- [x] `it propagates an exception thrown by a callback`
- [x] `it clears every level without running callbacks`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
