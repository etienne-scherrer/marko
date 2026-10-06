# Task 008: PostgreSQL integration tests

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Against a real PostgreSQL server (group `integration`, skipped without `MARKO_TEST_PGSQL_HOST`): a real two-session deadlock, `noWait()` lock failure, SERIALIZABLE conflicts and a retried transaction. Fix the stale `PDOException` expectations in the touched file.

## Context
- Related files: packages/database-pgsql/tests/Integration/TransactionPrimitivesTest.php, new ConcurrencyErrorsTest.php, contender script under tests/Fixtures/Concurrency/
- A deadlock needs a second process (proc_open); poll pg_stat_activity for the contender's lock wait instead of sleeping.

## Requirements (Test Descriptions)
- [x] `it raises DeadlockException in one of two deadlocked sessions`
- [x] `it raises LockTimeoutException when noWait meets a locked row`
- [x] `it raises SerializationFailureException for a concurrent update under SERIALIZABLE`
- [x] `it raises SerializationFailureException at COMMIT for a write skew under SERIALIZABLE`
- [x] `it retries a serialization failure and commits on the next attempt`

## Acceptance Criteria
- Passes locally against postgres:17

## Implementation Notes
tests/Integration/ConcurrencyErrorsTest.php with a proc_open() contender (tests/Fixtures/Concurrency/deadlock-contender.php); also lock_timeout -> LockTimeoutException. Fixed the stale PDOException expectations in TransactionPrimitivesTest. Verified against postgres:17 (5 consecutive green runs).
