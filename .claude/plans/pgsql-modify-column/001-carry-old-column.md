# Task 001: Carry the old column through TableDiff and DiffCalculator

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add an additive `columnsToModifyFrom` property to `TableDiff` holding the database's current definition of every modified column, populate it in `DiffCalculator`, and add the `MigrationException` factories the generators need.

## Context
- Related files: packages/database/src/Diff/TableDiff.php, packages/database/src/Diff/DiffCalculator.php, packages/database/src/Exceptions/MigrationException.php
- Patterns to follow: `MigrationException::partialIndexNotSupported()`

## Requirements (Test Descriptions)
- [x] `it puts the database column into columnsToModifyFrom for a nullability-only change`
- [x] `it puts the database column into columnsToModifyFrom for a default-only change`
- [x] `it keys columnsToModifyFrom by the same column names as columnsToModify`
- [x] `it defaults columnsToModifyFrom to an empty array`
- [x] `it builds a missing previous column exception naming the table and column`

## Acceptance Criteria
- All requirements have passing tests
- `TableDiff` constructor change is additive

## Implementation Notes
(Left blank - filled in by programmer during implementation)
