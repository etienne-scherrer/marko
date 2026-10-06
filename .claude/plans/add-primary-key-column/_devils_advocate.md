# Devil's Advocate Review: add-primary-key-column

## Critical (Must fix before building)

1. **Field name mismatch (001, 002, 003, _plan.md).** The plan names the new field `TableDiff::$primaryKeyFrom`. The worktree already has task 001's code, and it uses `TableDiff::$currentPrimaryKey` (`TableDiff.php:38`, `DiffCalculator.php:96`, and the MySQL tests at `MySqlGeneratorTest.php:1257`). A task 003 worker reading the plan would build against a property that does not exist. Fix: rename every plan reference to `currentPrimaryKey`.

## Important (Should fix before building)

2. **Dropping the key and adding a new one in the same diff is not specified (002, 003).** Example: an entity swaps `uuid` (the current key) for a new `id` key, so `currentPrimaryKey = ['uuid']` and `uuid` is also in `columnsToDrop`. MySQL emits drops before adds, so a worker could subtract the dropped columns and let the add through. PostgreSQL emits adds before drops (`PgSqlGenerator::generateTableAlterations()`), so its `ADD PRIMARY KEY` would fail with "multiple primary keys". Replacing a key is out of scope. Fix: both generators throw `primaryKeyAlreadyExists` whenever `currentPrimaryKey` is non-empty, even if those columns are being dropped. Added as a requirement in both tasks.

3. **Dropping part of a composite key breaks the new down path (002, 003).** Take a table with key `(a, b)` whose entity drops `b`:
   - MySQL shrinks the key to `(a)`. The new down restore then emits `ADD COLUMN b ..., ADD PRIMARY KEY (b)`, which fails because a key already exists.
   - PostgreSQL drops the whole `_pkey` constraint. Every later diff then throws `columnChangeNotSupported` for `a`.

   Both are silent key changes, and that is out of scope. Fix: up throws `columnChangeNotSupported($table, $column, $driver, 'primary key')` when `columnsToDrop` holds some but not all of `currentPrimaryKey`. Down then only restores a key when the up dropped the whole key, using `ADD PRIMARY KEY` over the dropped key columns.

4. **The MySQL primary-key-change check has to come before the render-equality skip and cover more than "only" (002).** The requirement says "only changes its primary key". If the check sits after the `buildColumnDefinition` equality test in `generateColumnModifications()`, a column that changes type *and* key flag emits `MODIFY COLUMN`. MODIFY never adds or drops a key, so the key change is silently lost. Fix: throw whenever `target->primaryKey !== previous->primaryKey`, in both directions, before any skip. This matches PostgreSQL's `generateModifyColumnIfChanged()`. Requirement added.

5. **The success criterion "non-auto-increment key column on a table with rows applies" cannot hold (_plan.md, 004, 005).** A key column with no per-row default fails at the database when the table has two or more rows. PostgreSQL rejects it because the column contains NULLs. MySQL/MariaDB fill an implicit `0`/`''` and fail with a duplicate entry. Fix: narrow the criterion. Add real-database tests for:
   - a non-auto-increment key added to an empty table
   - a no-default key on a table with rows, which fails loudly and leaves the table unchanged (one statement, so atomic)

## Minor (Nice to address)

- `currentPrimaryKey` is filled in column order (filter over `$databaseTable->columns`), not key order. Both introspectors' `getPrimaryKey()` already return key order (`SEQ_IN_INDEX` / `array_position`). This only matters if the down restore uses it to rebuild a composite key, so prefer key order there.
- `MigrationException::primaryKeyAlreadyExists()` and its tests already exist (`MigrationException.php:109`), so task 001's fourth requirement is already met.
- The public `SqlGeneratorInterface::generateAddColumn()` still renders a key column with no key when called directly. Only the diff path gets the combined statement. Worth a docblock note.
- If one migration both adds the new key to table B and adds a foreign key on table A that references it, it only works if B is altered first (there is no ordering between `tablesToAlter`). This gap existed before this plan.

## Questions for the Team

- **Production drift check.** The new MySQL throw for a key-flag change propagates through `MigrateCommand::reportDrift()`, which turns any `MigrationException` into exit code 1. A MySQL production deploy that used to pass with silent key-flag drift will now fail after its migrations are applied. PostgreSQL already behaves this way. Is that acceptable, or should the release notes / `database-mysql.md` call it out? Task 006 now asks for a note.
- **MariaDB 10.11 and `DEFAULT (UUID())` (004).** The plan relies on the default being evaluated once per existing row. If the real-database test shows it is evaluated once for the whole table, what is the fallback: refuse expression-default key columns on MariaDB, or document a hand-written migration?
