# Plan: MySQL MODIFY COLUMN Fidelity

## Created
2026-10-05

## Status
completed

## Objective
Make MySQL column-modification migrations change only what the diff says changed: keep an undeclared length and default (the same tolerance rule PostgreSQL uses, now shared in `marko/database`), skip no-op `MODIFY COLUMN` statements, and make rollbacks restore the native definition (`DECIMAL(p,s)`, `UNSIGNED`, collation, `ON UPDATE CURRENT_TIMESTAMP`).

## Related Issues
Closes #252. Relates to #253 (edits the same generators later; not implemented here).

## Discovery Notes
- `PgSqlGenerator::targetColumn()` (from #243) mirrors `Column::equals()` tolerances (undeclared length/default keep the database's; auto-increment PK keeps nullability). `MySqlGenerator` has no equivalent: `generateTableAlterations()` restates the entity column, `mapType()` turns a length-less VARCHAR into `VARCHAR(255)`, and `buildColumnDefinition()` omits `DEFAULT` when the entity declares none.
- `MySqlIntrospector::getColumns()` reads only `DATA_TYPE`, `CHARACTER_MAXIMUM_LENGTH`, `IS_NULLABLE`, `COLUMN_DEFAULT`, `EXTRA`. `Column` has no precision/scale/unsigned/collation/on-update fields.
- On MySQL, entity type names and introspected names differ (`integer` vs `INT`, `boolean` vs `TINYINT`), so whether "the entity redefines the type" must be judged on the MySQL-mapped base type, not on `Column::typeEquals()`. That decision therefore lives in `MySqlGenerator`; `Column::resolveAgainst()` only carries the database's native metadata forward (the entity cannot declare it, exactly like an undeclared default).
- The inline `UNIQUE` keyword in `MODIFY COLUMN` adds a second unique index on every restatement; uniqueness is applied by the index diff (as on PostgreSQL), so a modify statement omits it.
- Root `tests/Integration/QueryBuilderRawConsistencyTest.php` is the precedent for a cross-driver consistency test.
- MySQL integration tests live in `packages/database-mysql/tests/Integration`, use `IntegrationDatabase::config()`, group `integration-services`; CI runs them against `mysql:8.4` (#226).

## Scope

### In Scope
- `Column`: optional `nativeType`, `collation`, `onUpdateExpression` (null defaults, ignored by `equals()`), carried by every `with*()` method; `resolveAgainst(Column $previous): Column` holding the shared tolerance rule.
- `PgSqlGenerator` uses `resolveAgainst()` (no behaviour change).
- `MySqlIntrospector` reads `COLUMN_TYPE`, `COLLATION_NAME` and the `ON UPDATE` part of `EXTRA`.
- `MySqlGenerator`: up path modifies to the resolved target, skips no-op modifies (both directions), emits native type / `COLLATE` / `ON UPDATE` where they still apply, omits inline `UNIQUE` in `MODIFY COLUMN`, requires the previous column for up as well as down.
- Cross-driver consistency test; MySQL integration round-trip comparing `SHOW CREATE TABLE`.
- Docs: `database-mysql.md`, `database.md` Column Changes section.

### Out of Scope
- Expression defaults / `USING` casts (#253).
- Changing `Column::equals()` semantics (e.g. `integer`/`INT` aliasing).
- Primary key / auto-increment change refusal on MySQL.

## Success Criteria
- [x] MySQL up migrations keep an undeclared length and default (unit test per case; shared test runs the same TableDiff through both generators)
- [x] No `MODIFY COLUMN` when the target renders the same as the previous column
- [x] Rollback restores `DECIMAL(p,s)`, `UNSIGNED`, collation, `ON UPDATE CURRENT_TIMESTAMP` (unit + MySQL integration round-trip on `SHOW CREATE TABLE`)
- [x] Tolerance rule lives in `Column::resolveAgainst()`, used by both generators
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Column native metadata and resolveAgainst() | - | completed |
| 002 | PgSqlGenerator uses resolveAgainst() | 001 | completed |
| 003 | MySqlIntrospector reads native type, collation, ON UPDATE | 001 | completed |
| 004 | MySqlGenerator modifies to the resolved target with native fidelity | 001 | completed |
| 005 | Cross-driver consistency test | 002, 004 | completed |
| 006 | MySQL integration round-trip test | 003, 004 | completed |
| 007 | Documentation | 004 | completed |

## Architecture Notes
- The tolerance rule (what the up migration moves to) is defined once, next to `Column::equals()`.
- Driver-native metadata is opaque to `marko/database`; each generator decides when it still applies. PostgreSQL's introspector leaves it null.
- A MySQL native type applies to a target column when its base type (word before `(`/space, lowercased) matches either the base of `mapType()` for the target or the target's own lowercased type name (so an introspected `ENUM`/`BINARY` column, which `TYPE_MAP` remaps to VARCHAR/BLOB, keeps `enum(...)`/`binary(n)`), and, for `char`/`varchar`/`binary`/`varbinary`, the length matches too. Integer display widths are ignored.
- An inherited (undeclared) length is used only when the previous column is char/varchar/binary/varbinary; otherwise `mapType()`'s default applies (TEXT reports a 65535 length that would yield an invalid `VARCHAR(65535)`).
- `COLLATE` is emitted only for string-family native/mapped types (char, varchar, *text, enum, set); `ON UPDATE` only for `timestamp`/`datetime`.
- Inline `UNIQUE` is omitted only in `MODIFY COLUMN`; CREATE TABLE / ADD COLUMN keep it. Public `generateModifyColumn()` still always returns a statement for the column it is given; resolve and no-op skip live in the table-alteration loops.

## Risks & Mitigations
- Fake introspector rows in unit tests lack the new keys: update fixtures (the query always selects them).
- #253 edits `formatDefault()` in the same files: keep changes there minimal.
