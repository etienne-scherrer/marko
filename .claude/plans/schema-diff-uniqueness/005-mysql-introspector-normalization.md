# Task 005: MySQL introspector normalization

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
MySqlIntrospector stops hiding single-column unique indexes, maps DATA_TYPE/COLUMN_TYPE to the abstract type names
entities use, casts COLUMN_DEFAULT to the column's PHP type, and normalizes MariaDB's quoted literals, `NULL` and
`current_timestamp()`.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, packages/database-mysql/tests/Introspection/MySqlIntrospectorTest.php

## Requirements (Test Descriptions)
- [x] `it returns single-column unique indexes from getIndexes`
- [x] `it maps MySQL data types to the abstract type names entities use`
- [x] `it reads tinyint(1) as boolean and char(36) as uuid`
- [x] `it casts integer, boolean and decimal defaults to their PHP types`
- [x] `it unquotes MariaDB string defaults`
- [x] `it reads the MariaDB NULL default as no default`
- [x] `it reads MariaDB unquoted defaults as expressions and current_timestamp() as CURRENT_TIMESTAMP`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `packages/database-mysql/tests/Integration/ModifyColumnMigrationTest.php` line ~120 asserts a decimal default of
  `'0.0000'` (string). With typed defaults it becomes a float. Update that expectation in this task; integration tests
  are skipped without a server, so the break would only surface in CI.
- MariaDB detection: `MySqlIntrospector` is `readonly`, so the version cannot be cached in a property; one
  `SELECT VERSION()` per `getColumns()` is accepted. An empty or missing result (the unit-test fakes match on SQL
  substrings and return `[]` for anything unmatched) must read as MySQL, not throw.
- Removing the `$uniqueColumns` parameter from `getIndexes()` is safe: `IntrospectorInterface::getIndexes()` declares
  only `$table`.
- The abstract type map must agree with `EntityMetadataFactory::TYPE_MAP` and MySqlGenerator's `TYPE_MAP`
  (`int`->`integer`, `tinyint(1)`->`boolean`, `char(36)`->`uuid`, `decimal`, `varchar`, `text`, `json`, `timestamp`,
  `datetime`, ...). Keep unknown types lower-cased as reported.
