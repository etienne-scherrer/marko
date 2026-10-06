# Task 004: MySqlGenerator modifies to the resolved target with native fidelity

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Build up `MODIFY COLUMN` from `Column::resolveAgainst()`, skip statements whose target renders the same as the previous column (up and down), and restate native type, `COLLATE` and `ON UPDATE` where they still apply.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php, packages/database-mysql/tests/Sql/MySqlGeneratorTest.php
- Patterns to follow: `PgSqlGenerator::generateColumnModifications()`

## Requirements (Test Descriptions)
- [x] `it keeps the existing VARCHAR length when the entity declares none`
- [x] `it keeps the existing default when the entity declares none`
- [x] `it emits no MODIFY COLUMN when the target matches the previous column`
- [x] `it keeps DECIMAL precision and UNSIGNED when the entity does not redefine the type`
- [x] `it emits the entity type and keeps the collation when a string column changes length`
- [x] `it drops the collation when a string column becomes non-string`
- [x] `it restores DECIMAL precision, UNSIGNED, collation and ON UPDATE in a down migration`
- [x] `it omits inline UNIQUE from MODIFY COLUMN`
- [x] `it throws a MigrationException when an up migration lacks the previous column`
- [x] `it restores a native enum and binary column in a down migration` (previous type `ENUM`/`BINARY`, nativeType `enum('a','b')`/`binary(16)`; must not become VARCHAR/BLOB)
- [x] `it keeps a native enum when the entity type is enum and only nullability changes`
- [x] `it does not inherit a TEXT length when a column becomes a string without a length` (previous `TEXT` length 65535 -> `VARCHAR(255)`, never `VARCHAR(65535)`)
- [x] `it keeps inline UNIQUE in CREATE TABLE and ADD COLUMN`

## Acceptance Criteria
- All requirements have passing tests
- Existing `MySqlGeneratorTest` / `MySqlIntegrationTest` direct `generateModifyColumn()` expectations still pass

## Implementation Notes
- Native type applies when its base (word before `(`/space, lowercased) equals the base of `mapType()` for the target OR the target's lowercased type name; for char/varchar/binary/varbinary the length must match too (see _plan.md Architecture Notes).
- Inherited length (entity declared none) is used only when the previous column's base type is char/varchar/binary/varbinary; otherwise let `mapType()` default.
- Omit inline `UNIQUE` only for MODIFY COLUMN (e.g. a flag on `buildColumnDefinition()`); CREATE TABLE / ADD COLUMN unchanged.
- Public `generateModifyColumn()` (interface method, returns `string`) keeps rendering the `$column` it is given; `resolveAgainst()` and the no-op skip (compare rendered definitions of target and previous) live in `generateTableAlterations()` / `generateReverseTableAlterations()`, mirroring `PgSqlGenerator::generateColumnModifications()`.
- Replace the `?? new Column(name: $columnName, type: 'string')` fallback in the up loop with `$tableDiff->previousColumn($columnName)`.
- Keep `formatDefault()` untouched where possible (#253 edits it).
