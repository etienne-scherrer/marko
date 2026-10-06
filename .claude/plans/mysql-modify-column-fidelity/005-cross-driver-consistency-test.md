# Task 005: Cross-driver consistency test

**Status**: completed
**Depends on**: 002, 004
**Retry count**: 0

## Description
A root-level test runs the same `TableDiff` through `MySqlGenerator` and `PgSqlGenerator` and asserts both keep the undeclared length and default.

## Context
- Related files: tests/Integration/QueryBuilderRawConsistencyTest.php (precedent)

## Requirements (Test Descriptions)
- [x] `it keeps an undeclared length on both drivers`
- [x] `it keeps an undeclared default on both drivers`
- [x] `it emits nothing on either driver when only an accepted difference remains`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Added to the existing `packages/database/tests/Feature/DriverParityTest.php`, which already runs both generators side by side, instead of a new root-level file.
