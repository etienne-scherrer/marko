# Task 009: MySQL integration tests

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Against a real MySQL server (group `integration`, skipped without `MARKO_TEST_MYSQL_HOST`): a real two-session deadlock, `noWait()` (3572), lock wait timeout (1205), and a deadlock inside a nested transaction retried at the outermost level. Fix stale `PDOException` expectations in the touched file.

## Context
- Related files: packages/database-mysql/tests/Integration/TransactionPrimitivesTest.php, new ConcurrencyErrorsTest.php, contender script under tests/Fixtures/Concurrency/

## Requirements (Test Descriptions)
- [x] `it raises DeadlockException in one of two deadlocked sessions`
- [x] `it raises LockTimeoutException when noWait meets a locked row`
- [x] `it raises LockTimeoutException when the lock wait timeout expires`
- [x] `it surfaces the deadlock from a nested transaction and retries at the outermost level`

## Acceptance Criteria
- Passes locally against mysql:8.4

## Implementation Notes
Same layout as 008; contender updates rows 2..40 so this session is the smaller transaction and InnoDB picks it as the victim, making the nested-retry test deterministic. Fixed stale PDOException expectations in TransactionPrimitivesTest. Verified against mysql:8.4 (5 consecutive green runs).
