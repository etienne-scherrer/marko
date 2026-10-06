# Task 002: ExpressionDefaultCanonicalizer

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
A service in `Marko\Database\Diff` that takes the entity and database schemas and returns the entity schema with each matching `Expression` default replaced by the database column's default, asking the introspector (when it implements `ExpressionDefaultMatcherInterface`) only for columns that actually differ.

## Context
- Related files: packages/database/src/Diff/, packages/database/tests/Diff/
- Patterns to follow: DiffCalculator (pure, array<string, Table>)

## Requirements (Test Descriptions)
- [x] `it replaces the entity expression default with the database default when the database stores the same expression`
- [x] `it keeps the entity expression default when the database stores a different expression`
- [x] `it does not probe columns whose defaults already compare equal`
- [x] `it does not probe columns whose entity default is not an expression`
- [x] `it does not probe columns the database does not have or has no default for`
- [x] `it does not probe columns that differ in more than their default`
- [x] `it returns the entity schema unchanged when the introspector cannot match expression defaults`
- [x] `it lets the matcher's MigrationException for a rejected expression propagate`

## Acceptance Criteria
- All requirements have passing tests, using a counting fake introspector

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
