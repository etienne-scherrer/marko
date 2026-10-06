# Task 004: MySQL translator mappings

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Map error `1213` to `DeadlockException`, and `1205` and `3572` to `LockTimeoutException` in `MySqlExceptionTranslator`; update the doc comment.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlExceptionTranslator.php, packages/database-mysql/tests/Connection/MySqlExceptionTranslatorTest.php

## Requirements (Test Descriptions)
- [x] `it translates error 1213 into DeadlockException`
- [x] `it translates error 1205 into LockTimeoutException`
- [x] `it translates error 3572 into LockTimeoutException`
- [x] `it reads the error number from the message when errorInfo is empty`
- [x] `it still translates an unrelated error number into a plain QueryException`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
1213, 1205, 3572 mapped; added a test that parses 1213 from the message when errorInfo is empty.
