# Task 006: MySQL integration round-trip test

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Against real MySQL: create a table with `VARCHAR(500)`, `DECIMAL(12,4) UNSIGNED`, `INT UNSIGNED`, a `utf8mb4_bin` column and `ON UPDATE CURRENT_TIMESTAMP`, apply a generated up migration, roll it back, and compare `SHOW CREATE TABLE` with the original.

## Context
- Related files: packages/database-mysql/tests/Integration/, packages/database-pgsql/tests/Integration/ModifyColumnMigrationTest.php (pattern)

## Requirements (Test Descriptions)
- [x] `it keeps the length and default when only nullability changes`
- [x] `it restores SHOW CREATE TABLE exactly after up and down`
- [x] `it yields an empty diff after applying a generated change`

## Acceptance Criteria
- Passes against MySQL 8.4; skips without MARKO_TEST_MYSQL_HOST
- `pest()->group('integration-services')`, settings from `Marko\Database\MySql\Tests\Fixtures\IntegrationDatabase::config()`, table dropped in beforeEach/afterEach

## Implementation Notes
- Create the source table with raw DDL (the generator cannot express `DECIMAL(12,4) UNSIGNED` or a column collation).
- Entity tables must use MySQL type names (`int`, `varchar`, `decimal`, `timestamp`), not `integer`/`boolean`: `Column::equals()` does not alias them, so an abstract name never yields an empty diff on MySQL.
- Avoid numeric defaults in the empty-diff case (introspected defaults are strings, e.g. `'0.0000'`, never `===` a float); use string defaults.
