# Task 003: PostgreSQL Generator Adds Key Columns With Their Key

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`PgSqlGenerator` emits added primary-key columns and `ADD PRIMARY KEY (...)` in one `ALTER TABLE` instead of a key-less `ADD COLUMN`, throws when the table already has a key, and restores a dropped key column in down the same way.

## Context
- Related files: packages/database-pgsql/src/Sql/PgSqlGenerator.php
- Tests: packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php

## Requirements (Test Descriptions)
- [x] `it adds a serial primary key column and its key in one statement`
- [x] `it adds every column of a composite primary key and the key in one statement`
- [x] `it adds a uuid primary key column with its default and key in one statement`
- [x] `it throws when a primary key column is added to a table that already has a primary key`
- [x] `it adds a column that is not a key to a table that has a primary key`
- [x] `it keeps added columns that are not part of the key in their own statements`
- [x] `it throws when a primary key column is added while the current key's columns are dropped in the same diff`
- [x] `it throws when a diff drops part of a composite primary key` (`columnChangeNotSupported(..., 'PostgreSQL', 'primary key')`: PostgreSQL would drop the whole constraint)
- [x] `it drops an added primary key column in down`
- [x] `it restores a dropped primary key column with its key in down`

## Notes
- Read the current key from `TableDiff::$currentPrimaryKey` (not `primaryKeyFrom`); throw `MigrationException::primaryKeyAlreadyExists()` (already exists). The check ignores `columnsToDrop`: this generator emits ADD COLUMN before DROP COLUMN, so a replacement key would fail at the database.
- Keep `generateColumnDefinition(..., forAlter: true)` for the column parts and append `ADD PRIMARY KEY (...)`; mirror the statement shape of task 002's MySQL tests (`MySqlGeneratorTest.php`, "primary key columns on existing tables").

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
