# Task 004: MySQL and MariaDB Real-Database Tests

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Prove against MySQL 8.4, MariaDB 11.8 and MariaDB 10.11 that adding a key column to a table with rows applies, settles and rolls back.

## Context
- Related files: packages/database-mysql/tests/Integration/SchemaDiffSettlesTest.php
- Run with MARKO_TEST_MYSQL_* against each server (see tests/Integration/compose.yml)

## Requirements (Test Descriptions)
- [x] `it adds an auto-increment primary key column to a table with rows and the diff is then empty`
- [x] `it adds a uuid primary key column with a UUID() default to a table with rows and the diff is then empty`
- [x] `it adds a non-auto-increment primary key column without a default to an empty table and the diff is then empty`
- [x] `it fails loudly adding a primary key column without a default to a table with rows and leaves the table unchanged` (duplicate implicit default; one statement, so no half-applied column)
- [x] `it refuses to add a primary key column to a table that already has a primary key`
- [x] `it drops the added primary key column in down and the table matches the original`

## Acceptance Criteria
- All requirements pass on MySQL 8.4, MariaDB 11.8 and MariaDB 10.11

## Implementation Notes
- MySQL 8.4 with binary logging on (its default) refuses any `ADD COLUMN` with a non-deterministic default such as `(UUID())` with error 1674, key or not. The UUID test therefore asserts success with distinct keys on MariaDB (which evaluates the default per row) and a loud `QueryException` with the table unchanged on MySQL. The non-auto-increment key is also covered on MySQL by the empty-table test.
- Added `packages/admin-auth/tests/Integration/MySql/FreshMigrateTest.php` upgrade test: the tables of the old hand-written admin-auth migrations, with rows, are migrated by `db:migrate` alone (both pivot `id` keys added, rows numbered, second run has nothing to migrate) on all three servers.
