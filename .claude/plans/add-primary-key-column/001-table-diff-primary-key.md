# Task 001: TableDiff Carries the Current Primary Key

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
The SQL generators only see a `TableDiff`, so they cannot tell whether the table already has a primary key. Add the database's current primary-key column names to `TableDiff`, fill them in `DiffCalculator`, and add the `MigrationException` the generators throw when a key column is added to a table that already has a key.

## Context
- Related files: packages/database/src/Diff/TableDiff.php, packages/database/src/Diff/DiffCalculator.php, packages/database/src/Exceptions/MigrationException.php
- Tests: packages/database/tests/Diff/TableDiffTest.php, DiffCalculatorTest.php, packages/database/tests/Exceptions/

## Interface Contract
- `TableDiff::$currentPrimaryKey` (`list<string>`, default `[]`, last constructor parameter) — tasks 002/003 build against this name.
- `MigrationException::primaryKeyAlreadyExists(string $table, list<string> $existingColumns, list<string> $newColumns)` (already present).

## Requirements (Test Descriptions)
- [x] `it defaults the current primary key to an empty list`
- [x] `it fills the current primary key from the database table's key columns`
- [x] `it keeps the current primary key out of isEmpty so a key alone is no change`
- [x] `it names the table, the existing key columns and the new key columns when a primary key already exists`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
