# Task 002: Precedence for Constrained and Catch-all Routes

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Integrate constraints and catch-alls into #171's precedence rules: at equal static segment count, constrained routes sort before unconstrained ones, and catch-all routes sort after every other dynamic route.

## Context
- Related files: packages/routing/src/RouteCollection.php, packages/routing/src/RouteDefinition.php

## Requirements (Test Descriptions)
- [x] `it tries a constrained route before an unconstrained route with the same static segments`
- [x] `it falls through to the next route when a constraint rejects the value`
- [x] `it tries catch-all routes after other dynamic routes`
- [x] `it returns no match when the only candidate's constraint rejects the value`

## Acceptance Criteria
- All requirements have passing tests
- Existing precedence tests unchanged

## Implementation Notes
(Left blank - filled in by programmer during implementation)
