# Plan: Add Primary-Key Columns to Existing Tables

## Created
2026-10-06

## Status
completed

## Objective
Let `db:migrate` add a primary-key column to a table that already exists: both SQL generators emit the column and its `PRIMARY KEY` in one `ALTER TABLE`, and every primary-key change they cannot make fails loudly instead of half-applying or being skipped.

## Related Issues
Closes #346

## Discovery Notes
- `MySqlGenerator::generateAddColumn()` renders the column without `PRIMARY KEY` (only `generateCreateTable()` emits the key), so `ADD COLUMN id INT NOT NULL AUTO_INCREMENT` fails with error 1075 on MySQL 8.4 and MariaDB 11.8/10.11, and a non-auto-increment key column is added without its key.
- `PgSqlGenerator::generateAddColumn()` passes `forAlter: true`, which drops `PRIMARY KEY`; `ADD COLUMN id SERIAL` succeeds without a key and every later diff throws `columnChangeNotSupported(..., 'primary key')`.
- MySQL `generateColumnModifications()` skips a modified column whose definitions render the same, so a column whose only difference is `primaryKey` is silently skipped forever.
- The generators only see a `TableDiff`, which does not carry the database table's current primary key, so they cannot tell whether the table already has one. `DiffCalculator` has the introspected table and can fill it.
- The down migration drops added columns; dropping a key column drops the key with it on both servers. A dropped primary-key column re-added by the down migration hits the same bug, so the reverse path uses the same combined statement.
- `admin-auth.md` documents a hand-written `ADD COLUMN ... PRIMARY KEY` workaround (added by #340) that this removes.

## Scope

### In Scope
- `TableDiff::$currentPrimaryKey`: the database's current primary-key column names, filled by `DiffCalculator`
- `MigrationException::primaryKeyAlreadyExists()` naming the table, the existing key columns and the new ones
- MySQL and PostgreSQL: added primary-key columns and `ADD PRIMARY KEY (...)` in one `ALTER TABLE` (composite keys too), in up and in the down path that restores dropped key columns
- MySQL: a modified column whose primary-key flag changes throws `columnChangeNotSupported` (parity with PostgreSQL)
- Generator unit tests and real-database integration tests (MySQL 8.4, MariaDB 11.8, 10.11, PostgreSQL 17)
- Docs: `database.md` (which primary-key changes are generated), `admin-auth.md` upgrade note, driver docs where relevant

### Out of Scope
- Changing which existing columns form a key (drop/re-create a primary key); stays a hand-written migration
- Adding a key while the table already has one (throws), including when the same diff drops the current key's columns (replacing a key)
- Dropping part of a composite key (throws `columnChangeNotSupported(..., 'primary key')`: MySQL would shrink the key, PostgreSQL would drop the whole constraint)

## Success Criteria
- [x] Adding an auto-increment key column, or a key column with a per-row expression default (UUID), to an existing table with rows applies on every server and the next diff is empty; a non-auto-increment key column without a default applies to an empty table, and on a table with rows fails loudly at the database leaving the table unchanged
- [x] Adding a primary-key column to a table that already has a key throws a `MigrationException` naming the table and both key column sets
- [x] MySQL throws for a modified column whose `primaryKey` flag differs (alone or with other changes), in up and down
- [x] The down migration drops the added column and key, and the table matches the original
- [x] Docs updated; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | TableDiff carries the current primary key; new MigrationException | - | completed |
| 002 | MySQL generator adds key columns with their key in one statement | 001 | completed |
| 003 | PostgreSQL generator adds key columns with their key in one statement | 001 | completed |
| 004 | MySQL/MariaDB real-database tests | 002 | completed |
| 005 | PostgreSQL real-database tests | 003 | completed |
| 006 | Docs: database.md, admin-auth.md, driver pages | 002, 003 | completed |

## Architecture Notes
- One statement per table for all added key columns: `ALTER TABLE t ADD COLUMN a ..., ADD COLUMN b ..., ADD PRIMARY KEY (a, b)`. Non-key added columns keep their own statements.
- The "table already has a key" check uses `TableDiff::$currentPrimaryKey` (already implemented as `list<string>`, default `[]`); a hand-built `TableDiff` without it is treated as a table without a key. The check ignores `columnsToDrop`: a non-empty `currentPrimaryKey` plus an added key column always throws, on both drivers (PostgreSQL emits adds before drops, so subtracting drops would only work on MySQL).
- `MigrationException::primaryKeyAlreadyExists(string $table, list<string> $existingColumns, list<string> $newColumns)` already exists.
- Down restores a key over the dropped columns only when up dropped the whole key; up refuses a partial composite-key drop, so down never sees one.
- MySQL's primary-key-flag check runs before `generateColumnModifications()`'s render-equality skip, mirroring `PgSqlGenerator::generateModifyColumnIfChanged()`.

## Risks & Mitigations
- MariaDB/MySQL evaluating a `UUID()` expression default once for all existing rows would make the key fail with duplicates: covered by a real-database test with two existing rows.
- `TableDiff` gains a constructor parameter: added last with a default, so existing named-argument callers are unaffected.
