# Task 005: database-readwrite still overrides the shared driver connection

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Prove that with the real pgsql/mysql manifests registered and `database-readwrite`'s boot callback run, `ConnectionInterface` and `TransactionInterface` still resolve to the `ReadWriteConnection`.

## Context
- Related files: packages/database-readwrite/module.php, packages/database-readwrite/tests/Module/

## Requirements (Test Descriptions)
- [x] `it resolves ConnectionInterface to the ReadWriteConnection over the shared pgsql binding`
- [x] `it resolves TransactionInterface to the same ReadWriteConnection`
- [x] `it resolves ConnectionInterface to the ReadWriteConnection over the shared mysql binding`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
