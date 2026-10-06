# Task 002: Integration tests for RETURNING on MariaDB, MySQL unchanged

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Real-database coverage in `GeneratedPrimaryKeysTest.php`, branching on `IntegrationDatabase::isMariaDb()`: generated UUID keys through `save()` and `insertBatch()` and auto-increment `insertBatch()` through RETURNING on MariaDB (CI runs 11.8 and 10.11); the loud error and offset ids stay on MySQL 8.4.

## Context
- Related files: packages/database-mysql/tests/Integration/GeneratedPrimaryKeysTest.php, packages/database-mysql/tests/Fixtures/IntegrationDatabase.php
- Run against local containers with MARKO_TEST_MYSQL_* and MARKO_TEST_MYSQL_SERVER
- The existing `it throws RepositoryException when saving an unset generated key on MySQL` currently runs on MariaDB too and fails once task 001 lands: guard it (and the new MySQL-only test) with `IntegrationDatabase::isMariaDb()` skips, and guard the MariaDB tests the other way. Rewrite the file header comment, which says MariaDB is treated like MySQL.
- The auto-increment `insertBatch()` tests need their own entity and table (for example `auto_increment_tokens`, `INT AUTO_INCREMENT PRIMARY KEY`), created and dropped in `beforeEach`/`afterEach` next to `generated_key_tokens`.
- On MariaDB every auto-increment `insertBatch()` now goes through `query("... RETURNING ...")` instead of `execute()` + `lastInsertId()`. Existing MariaDB-run integration tests that call `insertBatch()` take that new path: `tests/Integration/ReservedWordIdentifiersTest.php` (reserved-word key column in the RETURNING clause and the result row key) and `tests/Integration/ConstraintViolationTest.php` (violation translated from `query()`). Run the whole `integration-services` group for `packages/database-mysql` (and the MySQL integration tests in `packages/admin-auth` and `packages/queue-database`) against MariaDB 11.8 and 10.11, and fix anything the new path breaks.

## Requirements (Test Descriptions)
- [x] `it reports RETURNING support for the connected server`
- [x] `it reads a generated key back on save on MariaDB`
- [x] `it reads each generated key back in insert order on insertBatch on MariaDB`
- [x] `it reads auto-increment ids back with RETURNING on insertBatch on MariaDB`
- [x] `it throws RepositoryException when saving an unset generated key on MySQL`
- [x] `it assigns consecutive auto-increment ids on insertBatch on MySQL without RETURNING`

## Acceptance Criteria
- Passes on MySQL 8.4, MariaDB 11.8 and MariaDB 10.11
- The full database-mysql `integration-services` group (plus admin-auth and queue-database MySQL integration tests) passes on MariaDB 11.8 and 10.11

## Implementation Notes
Auto-increment RETURNING test sets auto_increment_increment = 5 so offset arithmetic would give wrong ids. Verified the MariaDB tests fail with supportsReturning() forced false. Full database-mysql integration group, admin-auth MySql and queue-database MySqlRoundTrip pass on MySQL 8.4, MariaDB 11.8 and 10.11.
