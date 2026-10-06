# Task 003: Wire canonicalizer into DiffCommand and MigrateCommand

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Both commands run the canonicalizer between building the schemas and calling `DiffCalculator::calculate()`.

## Context
- Related files: packages/database/src/Command/DiffCommand.php, MigrateCommand.php, packages/database/tests/Command/

## Requirements (Test Descriptions)
- [x] `it reports no changes when the database stores the entity expression default in its own spelling` (DiffCommand)
- [x] `it reports the column as modified when the database stores a different expression` (DiffCommand)
- [x] `it fails with a MigrationException naming the column when the database rejects the expression` (DiffCommand)
- [x] `it generates no migration when the database stores the entity expression default in its own spelling` (MigrateCommand)

- [x] `it reports no drift when the database stores the entity expression default in its own spelling` (MigrateCommand, non-development `reportDrift()` path)

## Wiring Details (from devil's advocate review)
- Add a required `ExpressionDefaultCanonicalizer $expressionDefaultCanonicalizer` as the LAST constructor parameter of both `DiffCommand` and `MigrateCommand` (the container autowires it; it takes the bound `IntrospectorInterface`). Call `canonicalize($entitySchema, $databaseSchema)` right before `diffCalculator->calculate()` in `DiffCommand::execute()` and `MigrateCommand::calculateDiff()` (which covers the generate, verbose and `reportDrift()` paths).
- Update every existing construction: `packages/database/tests/Command/Helpers.php` (~line 242) and `packages/database/tests/Command/MigrateCommandTest.php` (~lines 309, 695, 742), passing `new ExpressionDefaultCanonicalizer($introspector)` built from the same introspector.
- `createMigrateCommand()` stubs the calculator with a canned `SchemaDiff` (`createMigrateDiffCalculator()`), so the new MigrateCommand tests must use a real `DiffCalculator` and a stub introspector implementing `ExpressionDefaultMatcherInterface` (follow the pattern around line 695).
- `DiffCommand` has no try/catch: the rejected-expression `MigrationException` propagates (assert with `toThrow`). `MigrateCommand` already catches `MigrationException` and prints `Error: ...` with exit code 1.

## Acceptance Criteria
- All requirements have passing tests; existing command tests still pass

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
