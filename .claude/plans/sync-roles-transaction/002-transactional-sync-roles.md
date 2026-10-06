# Task 002: Transactional, batched syncRoles()

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Make `AdminUserRepository::syncRoles()` delegate to `PivotSync`, so the delete and batched inserts run in one transaction (savepoint when nested) with the no-transaction fallback.

## Context
- Related files: packages/admin-auth/src/Repository/AdminUserRepository.php, packages/admin-auth/tests/Unit/Repository/AdminUserRepositoryTest.php
- Patterns to follow: RoleRepositoryTest.php sync tests (lines ~228-380)
- Implementation: `(new PivotSync($this->connection))->replace('admin_user_roles', 'user_id', 'role_id', $userId, $roleIds);` (contract in task 001). Add `@throws Throwable` to the `syncRoles()` docblock.
- Transaction tests use `Marko\AdminAuth\Tests\Fixtures\SqliteSavepointConnection` from task 001 (it already has the `admin_user_roles` table). Don't redeclare a savepoint helper function in this file.
- **Remove the existing test** `it('syncs roles for a user via syncRoles')`. It asserts per-row inserts (`bindings [1, 10]`, then `[1, 20]`) and fails once inserts are batched. The first two requirements below replace it.

## Requirements (Test Descriptions)
- [x] `it replaces a user's roles with the new set`
- [x] `it clears all roles when given an empty role id list` (one DELETE, no INSERT)
- [x] `it inserts all new roles in a single multi-row insert` (bindings `[1, 10, 1, 20]`)
- [x] `it wraps the delete and insert in one transaction when the connection supports transactions`
- [x] `it rolls back and leaves roles unchanged when an insert fails mid-sync`
- [x] `it rolls back only its own changes when it fails inside an outer transaction`
- [x] `it lets the outer transaction commit after a failed sync is caught`
- [x] `it still syncs roles when the connection does not support transactions`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
syncRoles() delegates to PivotSync; unit tests use SqlitePermissionConnection (see task 001 notes).
