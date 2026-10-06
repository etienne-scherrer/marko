# Task 002: Column Default Equality for Expressions and Literals

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make `Column::equals()` compare defaults semantically: a shortcut string equals the matching `Expression`, expressions compare by normalized SQL, and a `Literal` equals the same plain string, so the diff never produces a perpetual modify.

## Context
- Related files: packages/database/src/Schema/Column.php, packages/database/tests/Schema/EqualsTest.php

## Requirements (Test Descriptions)
- [x] `it treats an entity expression default and the same introspected expression as equal`
- [x] `it treats a shortcut string default and the matching introspected expression as equal`
- [x] `it treats different expression defaults as different`
- [x] `it treats a literal default and the same plain string as equal`
- [x] `it treats a literal default and an expression with the same text as different`

## Acceptance Criteria
- All requirements have passing tests

- [x] `it exposes a public symmetric default comparison for generators`

## Implementation Notes
Column::canonicalDefault() maps shortcut strings to Expression and Literal to its string.

`Column::defaultEquals()` is currently `private` and asymmetric (entity `null` accepts any database default). Generators (task 003, PgSqlGenerator line ~497 uses `!==`) need a public, symmetric comparison that does NOT apply the "entity null accepts anything" rule: add `public function hasSameDefaultAs(Column $other): bool` (canonical comparison only) and have the private `defaultEquals()` build on it. Keep the asymmetric rule only inside `equals()`.
