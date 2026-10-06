# Task 002: Entity type synonym canonicalization in SchemaBuilder

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Entities may name a type with a generator synonym (`int`, `bool`, `string`). Canonicalize them to the vocabulary
introspectors report (`integer`, `boolean`, `varchar`) when building schema columns, and have SchemaRegistry reuse
SchemaBuilder instead of duplicating column/index building.

## Context
- Related files: packages/database/src/Entity/SchemaBuilder.php, packages/database/src/Schema/SchemaRegistry.php

## Requirements (Test Descriptions)
- [x] `it canonicalizes the int, bool and string type synonyms`
- [x] `it keeps other types as declared`
- [x] `it builds extender columns through the schema builder`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `SchemaBuilder::buildColumn()` and `buildIndex()` are private today; SchemaRegistry's extender merge
  (`registerEntities()`) needs them, so expose public methods on SchemaBuilder (it is not final; keep it that way)
  and delete SchemaRegistry's private duplicates. Extender columns must get the same canonicalization.
- Canonicalize only the schema `Column::$type`; leave `ColumnMetadata::$type` untouched (hydration/type casting read it).
- Both generators already render `integer`/`boolean`/`varchar` (MySqlGenerator falls back to `strtoupper()` for
  `varchar`), so canonicalization changes no generated SQL; add a generator-agnostic assertion only.
