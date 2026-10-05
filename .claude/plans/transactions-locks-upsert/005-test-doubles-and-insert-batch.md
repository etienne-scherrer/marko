# Task 005: Update TransactionInterface test doubles; insertBatch via transaction()

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
Every test double or fixture that implements TransactionInterface gains the new methods. Repository::insertBatch() calls transaction() instead of checking inTransaction() first.

## Context
- Related files: packages/database/src/Repository/Repository.php, and the doubles found by grepping for `function beginTransaction` across packages (database, queue-database, admin-auth, database-readwrite)

## Requirements (Test Descriptions)
- [x] `it wraps the batch insert in transaction() on a transactional connection`
- [x] `it nests the batch insert inside an outer transaction instead of skipping the wrap`
- [x] `it keeps every existing insertBatch test green` (except tests that asserted the old skip-when-active behaviour. Rewrite those. Make sure the doubles' `transaction()` invokes the callback and logs begin/commit/rollback, so the rollback-on-failure tests still prove something)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
