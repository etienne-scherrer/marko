# Task 003: Stubs Instead of Mocks in routing and core

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace `createMock()` with `createStub()` wherever the double never gets `expects()`, in `packages/routing/tests/RouterTest.php`, `packages/routing/tests/Middleware/MiddlewarePipelineTest.php` and `packages/core/tests/Command/CommandRunnerTest.php` (44 notices).

## Context
- Refactor of existing tests: the existing test names are the requirements; behaviour must not change.
- Run each file with `--display-phpunit-notices` to confirm zero notices.

## Requirements (Test Descriptions)
- [x] Existing routing and core tests pass with no PHPUnit notices
- [x] No `createMock()` remains in these files without an `expects()`
- [x] Helper type hints match the double type (`Stub` vs `MockObject`)

## Acceptance Criteria
- Zero notices for `packages/routing` and `packages/core`

## Implementation Notes
Replaced createMock with createStub for ContainerInterface doubles lacking expects()/with(); kept mocks where expects()/with() used. 0 notices, 445 tests pass.
