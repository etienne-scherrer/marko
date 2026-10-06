# Devil's Advocate Review: pgsql-modify-column

Note: when this review ran, the worktree already held work for tasks 001 and 002 (`TableDiff::previousColumn()`, `columnsToModifyFrom`, new PgSqlGeneratorTest cases). The findings below apply to that code as well as to the plan.

## Critical (Must fix before building)

### C1. The generator compares columns strictly, but the diff uses tolerances, so up migrations make destructive changes nobody asked for (002)
`Column::equals()` tolerates three cases: an entity length of `null` (accept whatever length the database has), an entity default of `null` (accept the database's default), and nullable mismatches on PK+auto-increment columns. `PgSqlGenerator::generateModifyColumn()` compares strictly instead: `mapType()` turns a `null` length into `VARCHAR(255)`, and it uses `$column->default !== $oldColumn->default` and `$column->nullable !== $oldColumn->nullable`. So once a column is flagged for any one reason (say a nullability change), the up migration also:
- runs `ALTER COLUMN TYPE VARCHAR(255)` on a `VARCHAR(500)` column the diff considered equal. That is a silent schema change, and it fails with "value too long" when existing rows are longer than 255.
- runs `DROP DEFAULT` on a database default that `defaultEquals()` deliberately accepts (the "migration default for NOT NULL columns" case).
- runs `DROP NOT NULL` on a serial primary key whose entity property is `?int $id`. PostgreSQL rejects this ("column is in a primary key").

The down migration then "restores" changes the up should never have made.

**Fix:** in `generateTableAlterations()`, build an *effective target* column before calling `generateModifyColumn()`: `length = new->length ?? old->length`, `default = new->default ?? old->default`, and `nullable = old->nullable` when `new->primaryKey && new->autoIncrement`. Up calls `generateModifyColumn($t, $effective, $old)` and down calls `generateModifyColumn($t, $old, $effective)`. `generateModifyColumn()` itself stays strict for direct callers. The requirement "emits DROP DEFAULT when the new column has no default" contradicts the diff tolerance on the up path. It now applies only to direct `generateModifyColumn()` calls and to down migrations where the old column had no default.

### C2. Task 002 needs exception factories that task 001 does not list (001 → 002)
`_plan.md` promises loud `MigrationException`s for "nothing to emit" and "PK/auto-increment change in place". Task 001's requirements only cover `missingPreviousColumn()`. A 002 worker would either invent the factories ad hoc or ship without them. **Fix:** task 002 now owns `MigrationException::emptyColumnModification(table, column)` and `MigrationException::columnChangeNotSupported(table, column, driver, reason)` in `packages/database`, each with its own requirement. The plan also now pins the order of checks: previous column lookup, then PK/AI difference (throw), then unique-only (skip), then `generateModifyColumn()` (throw if empty).

## Important (Should fix before building)

### I1. Down migrations quote introspected SQL-expression defaults (002)
`PgSqlIntrospector::parseDefault()` returns expressions as-is (`CURRENT_TIMESTAMP`, `now()`). `formatDefaultValue()` then quotes them, so the down migration for a `created_at` column whose default the entity changed emits `SET DEFAULT 'CURRENT_TIMESTAMP'` / `'now()'` and fails or misbehaves on rollback. This is new exposure: before this plan, down migrations never emitted defaults taken from the database. **Fix:** the modify path emits the same keyword list `MySqlGenerator::SQL_EXPRESSIONS` already uses (`CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`, `NOW()`, case-insensitive) unquoted. `gen_random_uuid()`-style function defaults stay out of scope, as the plan says.

### I2. Where modified columns are restored in down migrations is unspecified (002, 003)
In MySQL, a foreign key re-added in down needs the column type it was originally built on. Restore modified columns after added FKs/indexes/columns are dropped and dropped columns are re-added, and **before** dropped indexes and FKs are re-added. This applies to both drivers.

### I3. Existing tests that hand-build modify diffs (002, 003)
Hand-built `TableDiff`s with `columnsToModify` but no `columnsToModifyFrom` now throw. `PgSqlGeneratorTest`, `MySqlGeneratorTest`, `DiffCommandTest`, `TableDiffTest`, and anything that calls `generateUp()`/`generateDown()` with such a diff must add `columnsToModifyFrom`. `composer test` must stay green. MySQL up does not need the previous column, so decide explicitly: either MySQL up stays tolerant of a missing previous column (as the plan intends) and only down throws, or both throw. Task 003 now says up stays tolerant.

### I4. The integration test can collide with the parallel #230 work (004)
The #230 deadlock work is editing `tests/Integration/*` in `database-pgsql`, and Pest test files declare namespaced global functions. **Fix:** task 004 creates a new file (`ModifyColumnMigrationTest.php`), uses a unique table name and unique helper-function names, only *reads* `SharedConnectionContainer::config()`, and drops its table in `afterEach`. It also builds the entity-side `Table` by hand with explicit lengths, so length tolerance does not add noise. It adds a case proving that a database default is preserved when the entity declares none (guards C1).

## Minor (Nice to address)

- **M1.** `ALTER COLUMN ... TYPE` has no `USING` clause, so `varchar` to `integer` fails in PostgreSQL. This is pre-existing. A loud PostgreSQL error is acceptable for now.
- **M2.** In one `ALTER TABLE`, PostgreSQL runs `DROP DEFAULT` before `TYPE` and `SET DEFAULT` after it. That ordering is good, but `TYPE` still converts the *old* default first. If the old default cannot be cast to the new type, the statement fails even though a new default is being set.
- **M3.** `SET NOT NULL` fails when rows contain NULLs. A loud failure is fine, but the docs could mention backfilling.
- **M4.** The MySQL down `MODIFY COLUMN` from an introspected column loses things the `Column` model does not carry: `DECIMAL` precision (always `DECIMAL(10,2)`), `UNSIGNED`, charset/collation and comments.
- **M5.** No MySQL integration test covers the down migration. The unit tests are mock-free string assertions, so a type round-trip problem (for example `tinyint` vs `TINYINT(1)`) would only surface against a real server.

## Questions for the Team

- **Q1.** MySQL up has the same tolerance bug as C1 (the full `MODIFY COLUMN` from the entity column drops a DB default the diff accepted and resizes `VARCHAR` to 255). This is pre-existing and the plan keeps it unchanged. Should MySQL up also use the effective-target column? It is a small change once the helper exists, and it is the same class of bug as #217.
- **Q2.** Should the effective-target helper live on `TableDiff`/`DiffCalculator`, so both drivers share it, instead of being private to `PgSqlGenerator`?
