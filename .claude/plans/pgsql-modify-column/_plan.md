# Plan: PostgreSQL Column Default and Nullability Migrations

## Created
2026-10-05

## Status
completed

## Objective
Carry the database's current column definition through the schema diff so the PostgreSQL generator applies default and nullability changes, and so both drivers reverse modified columns in down migrations.

## Related Issues
Closes #217

## Discovery Notes
- `DiffCalculator::findColumnsToModify()` stores only the entity (new) column; `TableDiff::$columnsToModify` has no old definition.
- `PgSqlGenerator::generateTableAlterations()` emits a type-only `ALTER COLUMN ... TYPE`, so default/nullability changes are never applied and the same migration is regenerated forever.
- `PgSqlGenerator::generateModifyColumn()` already emits `SET/DROP NOT NULL` and `SET/DROP DEFAULT` but returns `ALTER TABLE "t" ` (empty) when nothing it handles differs.
- `MySqlGenerator` up migrations are fine (full `MODIFY COLUMN` definition) but use a `'string'` placeholder for the old column; neither generator reverses `columnsToModify` in down migrations.
- `MigrationException` (packages/database) is the home for migration-generation errors (`partialIndexNotSupported` precedent).
- PostgreSQL integration tests run only when `MARKO_TEST_PGSQL_HOST` is set (skip otherwise).

## Scope

### In Scope
- Additive `TableDiff::$columnsToModifyFrom` (`array<string, Column>`), populated by `DiffCalculator`
- PostgreSQL up migrations call `generateModifyColumn()` with the real old column; never an empty `ALTER TABLE`
- Down migrations in both drivers restore the old column definition
- Loud `MigrationException` when `columnsToModifyFrom` lacks a modified column, when `generateModifyColumn()` has nothing to emit, and when PostgreSQL would need to change a primary key / auto-increment in place
- Docs: database.md migrations section; pgsql integration test

### Out of Scope
- Changing `columnsToModify` to a value object (breaking)
- Changing the diff's default/length/unique tolerances
- Function-call defaults (`gen_random_uuid()`) quoting, which is a separate pre-existing behaviour

## Success Criteria
- [x] DiffCalculator puts the old column into `columnsToModifyFrom` for nullability-only and default-only changes
- [x] PgSqlGenerator up emits SET/DROP NOT NULL, SET/DROP DEFAULT; type+default in one ALTER TABLE; no empty ALTER TABLE
- [x] Both generators' down migrations restore old type, nullability and default
- [x] pgsql integration test: migrate a changed default, re-diff is empty
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Carry the old column through TableDiff/DiffCalculator | - | completed |
| 002 | PgSqlGenerator applies and reverses column modifications | 001 | completed |
| 003 | MySqlGenerator reverses column modifications | 001 | completed |
| 004 | PostgreSQL integration test: modified default converges | 002 | completed |
| 005 | Documentation | 002, 003 | completed |

## Architecture Notes
- `TableDiff` change is additive (new trailing constructor param with default `[]`) so third-party `SqlGeneratorInterface` implementations and hand-built diffs keep compiling.
- Generators look up `columnsToModifyFrom[$name]` and throw `MigrationException::missingPreviousColumn()` when it is absent instead of guessing.
- A column flagged only for a `unique` difference produces no column statement in PostgreSQL (uniqueness is handled by the index diff).
- Generators must honour `Column::equals()` tolerances. PostgreSQL builds an *effective target* column (`length ?? old length`, `default ?? old default`, old nullability for PK+auto-increment) and diffs it against the old column. Otherwise a column flagged for one reason gets unrelated destructive changes (VARCHAR resize to 255, DROP DEFAULT, DROP NOT NULL on a serial PK). Up: `generateModifyColumn($t, $effective, $old)`. Down: `generateModifyColumn($t, $old, $effective)`.
- Order of PostgreSQL checks per modified column: missing previous column (throw), PK/auto-increment differs (throw `columnChangeNotSupported`), nothing to emit after tolerances (skip), `generateModifyColumn()` (throws `emptyColumnModification` if called with nothing to do).
- Task 002 owns the `emptyColumnModification` and `columnChangeNotSupported` factories. Task 001 owns `missingPreviousColumn`.
- Down migrations restore modified columns after added FKs/indexes/columns are dropped and dropped columns are re-added, and before dropped indexes/FKs are re-added.
- Defaults that are SQL expression keywords (`CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`, `NOW()`) are emitted unquoted in the modify path, so rollbacks work.
- MySQL up stays tolerant of a missing previous column. Only down requires it.
- Integration test lives in a new file with unique names to avoid colliding with the parallel #230 work.

## Risks & Mitigations
- Hand-built `TableDiff`s without `columnsToModifyFrom` now throw: loud error naming table and column, with a suggestion.
- MySQL up migration unchanged (doesn't need the old column) to keep existing behaviour.
