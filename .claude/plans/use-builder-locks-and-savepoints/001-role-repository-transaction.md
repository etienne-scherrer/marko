# Task 001: RoleRepository::syncPermissions() uses transaction()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the hand-rolled `inTransaction()` ownership logic with `$this->connection->transaction(...)` so the sync nests as a savepoint inside a caller's transaction, keeping a single commented fallback for connections without transactions (same as `Repository::insertBatch()`).

## Context
- Related files: packages/admin-auth/src/Repository/RoleRepository.php, packages/admin-auth/tests/Unit/Repository/RoleRepositoryTest.php
- Patterns to follow: Repository::insertBatch() transaction fallback
- Test connection: replace the logging fake with an in-memory SQLite connection that implements real savepoints

## Requirements (Test Descriptions)
- [x] `it wraps the delete and insert in one transaction when the connection supports transactions`
- [x] `it rolls back and leaves permissions unchanged when an insert fails mid-sync`
- [x] `it rolls back only its own changes when it fails inside an outer transaction`
- [x] `it lets the outer transaction commit after a failed sync is caught`
- [x] `it still syncs when the connection does not support transactions`

## Acceptance Criteria
- All requirements have passing tests
- No `inTransaction()` call left in admin-auth/src

## Implementation Notes
