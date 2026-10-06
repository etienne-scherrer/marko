# Task 005: Integration tests on both servers

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Replace the MariaDB skip and the "pins the limitation" test in TransactionPrimitivesTest with tests that run on MySQL 8.4 and MariaDB 11.8; add an integration check that MySqlServer agrees with the real server.

## Context
- Related files: packages/database-mysql/tests/Integration/TransactionPrimitivesTest.php, ServerIdentityTest.php, tests/Fixtures/IntegrationDatabase.php

## Requirements (Test Descriptions)
- [x] `it lets a second connection share-lock a row that is share-locked`
- [x] `it raises LockTimeoutException for a shared lock with noWait on a row held for update`
- [x] `it leaves out a row held for update from a shared lock with skipLocked`
- [x] `it detects the server MARKO_TEST_MYSQL_SERVER names`

## Acceptance Criteria
- No MariaDB skip left in TransactionPrimitivesTest
- Passes against MySQL 8.4 and MariaDB 11.8

## Implementation Notes
