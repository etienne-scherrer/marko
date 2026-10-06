# Task 005: Stubs Instead of Mocks in notification and notification-database

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace `createMock()` with `createStub()` wherever the double never gets `expects()`, in `packages/notification/tests/Unit` and `packages/notification-database/tests/Unit` (~34 notices).

## Context
- Refactor of existing tests: the existing test names are the requirements; behaviour must not change.

## Requirements (Test Descriptions)
- [x] Existing notification and notification-database tests pass with no PHPUnit notices
- [x] No `createMock()` remains in these files without an `expects()`
- [x] Helper type hints match the double type (`Stub` vs `MockObject`)

## Acceptance Criteria
- Zero notices for both packages

## Implementation Notes

Replaced createMock() with createStub() for doubles without expects()/with() in notification and notification-database tests. 0 notices, 96 tests pass, phpcs/cs-fixer clean.
