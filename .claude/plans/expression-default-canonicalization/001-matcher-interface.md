# Task 001: Matcher interface, Expression::unwrap, rejected-expression exception

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add the contract drivers implement to compare an entity's expression default with what the database stored, plus the shared helpers the drivers need.

## Context
- Related files: packages/database/src/Introspection/, packages/database/src/Schema/Expression.php, packages/database/src/Exceptions/MigrationException.php
- Patterns to follow: MigrationException static factories (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it declares matchesStoredDefault on ExpressionDefaultMatcherInterface`
- [x] `it strips parentheses that wrap the whole expression without changing its case`
- [x] `it keeps parentheses that do not wrap the whole expression`
- [x] `it names the table, column and expression in the rejected default expression exception`

## Acceptance Criteria
- All requirements have passing tests
- IntrospectorInterface unchanged (non-breaking)

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
