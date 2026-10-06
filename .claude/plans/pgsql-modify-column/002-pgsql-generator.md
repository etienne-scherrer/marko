# Task 002: PgSqlGenerator applies and reverses column modifications

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make PostgreSQL up migrations call `generateModifyColumn($table, $new, $old)` with the real old column, and down migrations call it with the arguments swapped. Never emit an empty `ALTER TABLE`.

## Context
- Related files: packages/database-pgsql/src/Sql/PgSqlGenerator.php, packages/database-pgsql/tests/Sql/PgSqlGeneratorTest.php, packages/database/src/Exceptions/MigrationException.php (+ its test)
- `Column::equals()` tolerates: entity `length === null` (accept the DB length), entity `default === null` (accept the DB default), nullable mismatch on `primaryKey && autoIncrement`. The generator MUST honour the same tolerances, or a column flagged for one reason gets unrelated destructive changes (VARCHAR(500) -> VARCHAR(255), DROP DEFAULT on an accepted DB default, DROP NOT NULL on a serial PK, which PostgreSQL rejects).

### Effective target column (required)
In `generateTableAlterations()` / `generateReverseTableAlterations()`, for each modified column:
1. `$old = $diff->previousColumn($name)` (throws `missingPreviousColumn`).
2. If `primaryKey` or `autoIncrement` differ between new and old -> throw `MigrationException::columnChangeNotSupported($table, $column, 'PostgreSQL', reason)`.
3. Build `$effective` = new column with `length: $new->length ?? $old->length`, `default: $new->default ?? $old->default`, `nullable: ($new->primaryKey && $new->autoIncrement) ? $old->nullable : $new->nullable`.
4. If `$effective` and `$old` produce no type/nullability/default difference (e.g. unique-only), emit nothing for this column.
5. Up: `generateModifyColumn($table, $effective, $old)`. Down: `generateModifyColumn($table, $old, $effective)`.

`generateModifyColumn()` itself stays strict (public API for direct callers) and throws `MigrationException::emptyColumnModification($table, $column)` instead of returning `ALTER TABLE "t" ` with nothing after it.

### Exception factories (owned by this task, in packages/database)
- `MigrationException::emptyColumnModification(string $table, string $column)`
- `MigrationException::columnChangeNotSupported(string $table, string $column, string $driver, string $reason)` with a suggestion such as "write this migration by hand"

### Expression defaults
`PgSqlIntrospector::parseDefault()` returns expressions such as `CURRENT_TIMESTAMP` / `now()` as raw strings. When `SET DEFAULT` is emitted in the modify path, do not quote values that match the keyword list `MySqlGenerator::SQL_EXPRESSIONS` already uses (`CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`, `NOW()`, case-insensitive). Without this, a rollback emits `SET DEFAULT 'CURRENT_TIMESTAMP'`. Other function-call defaults remain out of scope.

### Down ordering
Restore modified columns after dropping added FKs, indexes and columns and re-adding dropped columns, and BEFORE re-adding dropped indexes and FKs.

### Existing tests
Every existing test that hand-builds a `TableDiff` with `columnsToModify` and calls `generateUp()`/`generateDown()` must add `columnsToModifyFrom`.

## Requirements (Test Descriptions)
- [x] `it emits SET DEFAULT for a default-only change in an up migration`
- [x] `it emits DROP DEFAULT from generateModifyColumn when the new column has no default`
- [x] `it leaves the database default alone in an up migration when the entity declares no default`
- [x] `it keeps the database length in an up migration when the entity declares no length`
- [x] `it does not drop NOT NULL on an auto-increment primary key declared nullable in PHP`
- [x] `it emits SET NOT NULL when a column becomes required`
- [x] `it emits DROP NOT NULL when a column becomes nullable`
- [x] `it combines a type change and a default change in one ALTER TABLE`
- [x] `it emits no statement for a column whose only difference is handled by the index diff`
- [x] `it throws instead of returning an empty ALTER TABLE from generateModifyColumn`
- [x] `it throws a MigrationException when a primary key or auto-increment change is required`
- [x] `it restores the old type, nullability and default in a down migration`
- [x] `it drops the default in a down migration when the old column had none`
- [x] `it restores a CURRENT_TIMESTAMP default unquoted in a down migration`
- [x] `it restores modified columns before re-adding dropped foreign keys in a down migration`
- [x] `it throws a MigrationException naming the column when columnsToModifyFrom is missing it`
- [x] `it builds an empty column modification exception naming the table and column` (packages/database)
- [x] `it builds a column change not supported exception naming the table, column and driver` (packages/database)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- The empty-modification factory is named `MigrationException::nothingToModify($table, $column, $driver)`.
- The effective target is `PgSqlGenerator::targetColumn()`; `generateColumnModifications()` handles up and down.
- SQL-expression defaults are unquoted in `formatDefaultValue()` itself, which also fixes `CREATE TABLE` for `default: 'CURRENT_TIMESTAMP'` (PostgreSQL rejected the quoted form).
- The reverse alterations were reordered: drop added FKs, indexes, columns; re-add dropped columns; restore modified columns; re-add dropped indexes and FKs.
