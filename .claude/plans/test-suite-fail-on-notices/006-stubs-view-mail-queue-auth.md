# Task 006: Stubs Instead of Mocks in view, mail, queue and auth packages

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace `createMock()` with `createStub()` wherever the double never gets `expects()`, in view-latte, view-twig, mail, mail-log, queue-database, authentication and admin-auth tests (~30 notices).

## Context
- Refactor of existing tests: the existing test names are the requirements; behaviour must not change.

## Requirements (Test Descriptions)
- [x] Existing tests in these packages pass with no PHPUnit notices
- [x] No `createMock()` remains in these files without an `expects()`
- [x] Helper type hints match the double type (`Stub` vs `MockObject`)

## Acceptance Criteria
- Zero notices for these packages

## Implementation Notes
Converted createMock() to createStub() where no expects()/with() rule is used; DatabaseQueueTest helper now typed Stub. Tests pass, lint clean.
