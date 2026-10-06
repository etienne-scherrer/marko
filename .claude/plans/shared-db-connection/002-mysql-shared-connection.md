# Task 002: Share connection and bind TransactionInterface in mysql module

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Same as task 001 for `packages/database-mysql/module.php`. The existing `IntrospectorInterface` closure also resolves `ConnectionInterface` and will now receive the shared instance.

## Context
- Related files: packages/database-mysql/module.php, packages/database-mysql/tests/Module/

## Requirements (Test Descriptions)
- [x] `it resolves the same ConnectionInterface instance for two repositories`
- [x] `it gives the QueryBuilderFactoryInterface the same connection the repositories use`
- [x] `it resolves TransactionInterface to the shared ConnectionInterface instance`
- [x] `it throws a loud error when the bound connection does not implement TransactionInterface`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
