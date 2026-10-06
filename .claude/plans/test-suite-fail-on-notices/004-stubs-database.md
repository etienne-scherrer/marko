# Task 004: Stubs Instead of Mocks in database and database-mysql

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace `createMock()` with `createStub()` wherever the double never gets `expects()`, in the `packages/database/tests` Migration, Command and Feature tests and `packages/database-mysql/tests/Query/MySqlQueryBuilderFactoryTest.php` (~62 notices).

## Context
- Refactor of existing tests: the existing test names are the requirements; behaviour must not change.

## Requirements (Test Descriptions)
- [x] Existing database and database-mysql tests pass with no PHPUnit notices
- [x] No `createMock()` remains in these files without an `expects()`
- [x] Helper type hints match the double type (`Stub` vs `MockObject`)

## Acceptance Criteria
- Zero notices for `packages/database` and `packages/database-mysql`

## Implementation Notes
Replaced createMock with createStub except where expects() is used (MigratorTest line 31, DataMigratorIntegrationTest two repositories). Helper types now Stub. 0 notices.
