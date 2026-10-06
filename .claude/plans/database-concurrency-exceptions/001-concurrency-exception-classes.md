# Task 001: Concurrency exception classes

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `TransactionConflictException`, `DeadlockException`, `SerializationFailureException` and `LockTimeoutException` to `marko/database`, plus `TransactionException::invalidAttempts()`.

## Context
- Related files: packages/database/src/Exceptions/QueryException.php, UniqueConstraintViolationException.php, TransactionException.php
- Patterns to follow: #177 `fromDriverError()` factories; tests in packages/database/tests/Exceptions/

## Requirements (Test Descriptions)
- [x] `it builds a DeadlockException from a driver error with the SQLSTATE, SQL and redacted message`
- [x] `it builds a SerializationFailureException from a driver error`
- [x] `it builds a LockTimeoutException from a driver error`
- [x] `it marks deadlocks and serialization failures as retryable transaction conflicts`
- [x] `it does not make LockTimeoutException a TransactionConflictException`
- [x] `it never copies bound values into the message`
- [x] `it explains an attempts value below one`

## Acceptance Criteria
- All requirements have passing tests
- No HTTP mapping on the new classes

## Implementation Notes
Added abstract TransactionConflictException (isRetryable() true, shared retry suggestion), DeadlockException, SerializationFailureException, LockTimeoutException and TransactionException::invalidAttempts(). Tests: packages/database/tests/Exceptions/ConcurrencyExceptionTest.php (also asserts no HttpExceptionInterface).
