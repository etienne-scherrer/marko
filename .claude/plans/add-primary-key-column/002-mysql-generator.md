# Task 002: MySQL Generator Adds Key Columns With Their Key

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`MySqlGenerator` emits added primary-key columns and `ADD PRIMARY KEY (...)` in one `ALTER TABLE`, throws when the table already has a key, throws instead of skipping a modified column whose primary-key flag changes, and restores a dropped key column in down the same way.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php
- Tests: packages/database-mysql/tests/Sql/MySqlGeneratorTest.php

## Requirements (Test Descriptions)
- [x] `it adds an auto-increment primary key column and its key in one statement`
- [x] `it adds every column of a composite primary key and the key in one statement`
- [x] `it adds a non-auto-increment primary key column and its key in one statement`
- [x] `it keeps added columns that are not part of the key in their own statements`
- [x] `it throws when a primary key column is added to a table that already has a primary key`
- [x] `it throws when a modified column only changes its primary key`
- [x] `it throws when a modified column changes its primary key along with its type` (check runs before the render-equality skip)
- [x] `it throws when a primary key column is added while the current key's columns are dropped in the same diff`
- [x] `it throws when a diff drops part of a composite primary key` (`columnChangeNotSupported(..., 'MySQL', 'primary key')`)
- [x] `it drops an added primary key column in down`
- [x] `it restores a dropped primary key column with its key in down`

## Notes
- The "already has a key" check uses `TableDiff::$currentPrimaryKey` and ignores `columnsToDrop` (driver parity with PostgreSQL, which adds before it drops).

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
