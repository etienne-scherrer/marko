# Task 001: Index constraint flag and diff-owned uniqueness

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Make the index diff own uniqueness. Columns compare without `unique`; unique non-PK entity columns imply a derived
`<table>_<column>_unique` unique index that is satisfied by any existing full single-column unique index on that column.
Add `Index::$constraint` (driver metadata, ignored by equals()).

## Context
- Related files: packages/database/src/Diff/DiffCalculator.php, packages/database/src/Schema/Index.php,
  packages/database/tests/Diff/DiffCalculatorTest.php
- Patterns to follow: foreign keys are already matched by columns rather than name

## Requirements (Test Descriptions)
- [x] `it adds a unique index when an existing column becomes unique`
- [x] `it drops the unique index when an existing column stops being unique`
- [x] `it keeps an existing single-column unique index of a unique column whatever its name`
- [x] `it does not report a column as modified when only its unique flag differs`
- [x] `it does not derive a unique index for a column that is being added`
- [x] `it does not derive a unique index when the entity declares one on the column`
- [x] `it does not treat a partial unique index as the column's uniqueness`
- [x] `it drops a unique index on a foreign key column that is no longer unique`
- [x] `it adds a plain replacement index when it drops the only index of a foreign key column`
- [x] `it adds no replacement index when another index on the database table already leads with the foreign key column`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- FK-column guard (devil's advocate): `findIndexesToDrop()` currently keeps every single-column index on an FK column.
  Dropping the unique index of an FK column must stay possible, but on MySQL/InnoDB a column created with inline
  `UNIQUE` plus a foreign key has only that unique index, and `DROP INDEX` then fails with error 1553 ("needed in a
  foreign key constraint"). So when the diff drops a unique index whose single column carries an entity foreign key
  (`Column::$references !== null`) and no other database index has that column as its first column, add a plain
  Btree replacement index `<table>_<column>_index` to `indexesToAdd`. It stays settled afterwards: the existing FK
  guard keeps a single-column index on an FK column. Task 008 makes MySqlGenerator order the replacement correctly.
- `Index::$constraint` must be the last constructor parameter with default `false`, so existing named and positional
  `new Index(...)` calls keep working. `Index::equals()` must ignore it.
- `TableDiff`/`SchemaDiff` shapes do not change; derived and replacement indexes are ordinary `Index` objects.
