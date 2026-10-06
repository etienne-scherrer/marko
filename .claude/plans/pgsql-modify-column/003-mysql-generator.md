# Task 003: MySqlGenerator reverses column modifications

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Down migrations emit `MODIFY COLUMN` with the old column definition for every modified column, replacing the `'string'` placeholder with the real old column.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php, packages/database-mysql/tests/Sql/MySqlGeneratorTest.php
- Use `$tableDiff->previousColumn($name)` (task 001) for the old column. It throws `MigrationException::missingPreviousColumn`.
- Up stays tolerant: it must NOT require `columnsToModifyFrom`, so existing hand-built diffs keep working. When the previous column is present, pass it to `generateModifyColumn()` instead of the `'string'` placeholder. When it is absent, keep the placeholder. Only down throws.
- Down ordering: emit the restoring `MODIFY COLUMN` after dropping added FKs, indexes and columns and re-adding dropped columns, and BEFORE re-adding dropped indexes and FKs. A foreign key re-added in down needs the column's original type.
- Do not change the MySQL up output (see Questions in `_devils_advocate.md` about the up tolerance bug).
- Coordinate: issue #230 is being worked in parallel in `packages/database-mysql`. Keep edits confined to `MySqlGenerator` and its test.

## Requirements (Test Descriptions)
- [x] `it restores the old type, nullability and default of a modified column in a down migration`
- [x] `it restores modified columns before re-adding dropped foreign keys in a down migration`
- [x] `it restores a CURRENT_TIMESTAMP default unquoted in a down migration`
- [x] `it throws a MigrationException naming the column when a down migration lacks the previous column`
- [x] `it still generates an up MODIFY COLUMN from the new column alone`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
