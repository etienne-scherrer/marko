# Devil's Advocate Review: mysql-modify-column-fidelity

Note: tasks 001 and 002 are already implemented in the worktree (`Column::resolveAgainst()`, `clone()`-based `with*()`, PgSqlGenerator call site). Findings target 003-006.

## Critical (Must fix before building)

1. **Task 004 / _plan.md Architecture Notes: native-type rule drops `enum(...)` and `binary(n)` on rollback.** The rule "native base matches the base of `mapType()` for the target" fails for introspected columns whose `DATA_TYPE` is remapped by `TYPE_MAP`: `ENUM` -> `VARCHAR`, `BINARY` -> `BLOB`. The previous column (type `ENUM`, nativeType `enum('a','b')`) would fail its own check, so the down migration turns an enum into `VARCHAR(n)` and `binary(16)` into `BLOB`. The same rule makes an up migration for entity type `enum` (accepted as equal by `typeEquals()` alias) convert a native enum to VARCHAR, which is exactly "changing what the diff didn't change". Fix: the native type also applies when the target's lowercased type equals the native base type (always true for an introspected previous column).

## Important (Should fix before building)

2. **Task 004: inherited length can produce invalid DDL.** `resolveAgainst()` copies the previous length whenever the entity declares none. On MySQL, `CHARACTER_MAXIMUM_LENGTH` is set for TEXT (65535), MEDIUMTEXT etc. An entity changing a TEXT column to `string` without a length resolves to `VARCHAR(65535)`, which MySQL rejects for utf8mb4 ("Column length too big"). Fix: the MySQL generator uses an inherited length only when the previous column is char/varchar/binary/varbinary; otherwise fall back to `mapType()`'s default.
3. **Task 004: scope of the native-fidelity rendering is unspecified.** `buildColumnDefinition()` is shared by CREATE TABLE, ADD COLUMN and MODIFY COLUMN. The task must state: (a) inline `UNIQUE` is omitted only for MODIFY (CREATE/ADD keep it); (b) public `generateModifyColumn()` still always returns a statement (it is an interface method returning `string`; existing tests call it directly) and renders `$column` as given — resolve + no-op skip live in the table-alteration loops; (c) native type/COLLATE/ON UPDATE rendering applies wherever a column carries the metadata (so down-path re-creation of dropped columns/tables also benefits).
4. **Task 003: `EXTRA` parsing is under-specified.** MySQL 8.x reports `DEFAULT_GENERATED on update CURRENT_TIMESTAMP` and `on update CURRENT_TIMESTAMP(3)`; a naive `str_starts_with`/exact match misses both. Also fake rows in `MySqlIntrospectorTest` (13 rows) lack the new keys; the introspector should read them null-safely or every fixture must be updated.
5. **Task 006: "empty diff after applying" cannot pass with abstract type names.** `Column::equals()` does not alias `integer`/`INT`, `boolean`/`TINYINT`, so an entity using `integer` always diffs against MySQL. The entity tables in the test must use MySQL type names (`int`, `varchar`, `decimal`, `timestamp`), and avoid numeric defaults (introspected `'0.0000'` string never `===` a float). The source table must be created with raw DDL (the generator cannot express `DECIMAL(12,4) UNSIGNED` or `utf8mb4_bin`).

## Minor (Nice to address)

- Task 005: `packages/database/tests/Feature/DriverParityTest.php` already runs both generators side by side; adding the cases there avoids a second parity file.
- Task 004: introspected string defaults (`'0'`) vs entity int (`0`) render differently, so the no-op skip still emits a harmless MODIFY in that case (pre-existing diff churn).
- Inherited default across a type change (e.g. VARCHAR default `'draft'` -> `int` with no default) yields `INT DEFAULT 'draft'`; same rule as PostgreSQL, overlaps #253.
- Generated columns (`VIRTUAL GENERATED`/`STORED GENERATED` in EXTRA) and expression defaults (`DEFAULT_GENERATED` with `uuid()`) are still restated incorrectly by MODIFY; out of scope (#253) but worth a doc note.
- `text` entity vs native `mediumtext`: base mismatch means MODIFY shrinks to TEXT when another attribute changes.

## Questions for the Team

- Omitting inline `UNIQUE` from MODIFY removes the only path that currently adds uniqueness to an existing MySQL column: `DiffCalculator` treats entity `unique=true` vs DB `unique=false` as equal, and no Index is synthesized for unique columns. Accept as a pre-existing gap shared with PostgreSQL, or file a follow-up?
- `datetime`/`timestamp` are aliases in `typeEquals()`. Should a native `timestamp` survive an entity declaring `datetime` (diff says unchanged), or should MODIFY convert it?
