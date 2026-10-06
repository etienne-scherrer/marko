# Task 001: PivotSync helper used by RoleRepository

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extract `syncPermissions()`'s delete + chunked multi-row insert + `transaction()` logic into a small `PivotSync` class, and make `RoleRepository::syncPermissions()` delegate to it, so `syncRoles()` can share it without copying.

## Context
- Related files: packages/admin-auth/src/Repository/RoleRepository.php, new packages/admin-auth/src/Repository/PivotSync.php, new packages/admin-auth/tests/Unit/Repository/PivotSyncTest.php, new packages/admin-auth/tests/Fixtures/SqliteSavepointConnection.php, packages/admin-auth/tests/Unit/Repository/RoleRepositoryTest.php
- Patterns to follow: the existing syncPermissions() body; RoleRepositoryTest's `createRoleSavepointConnection()`; packages/admin-auth/tests/Fixtures/SqlitePermissionConnection.php (fixture class style)

### PivotSync contract (task 002 builds against this)
```php
namespace Marko\AdminAuth\Repository;

/** @internal */
class PivotSync   // not final (no final classes)
{
    public const int ROWS_PER_CHUNK = 500;

    public function __construct(private readonly ConnectionInterface $connection) {}

    /**
     * @param array<int> $relatedIds
     * @throws Throwable
     */
    public function replace(string $table, string $ownerColumn, string $relatedColumn, int $ownerId, array $relatedIds): void;
}
```
- `replace()` runs `DELETE FROM {table} WHERE {ownerColumn} = ?` followed by chunked `INSERT INTO {table} ({ownerColumn}, {relatedColumn}) VALUES (?, ?), ...`. It wraps both in `transaction()` when the connection is a `TransactionInterface` and runs them directly otherwise.
- Remove `RoleRepository::SYNC_ROWS_PER_CHUNK`. The chunk size lives only in PivotSync. `syncPermissions()` keeps its docblock and becomes `(new PivotSync($this->connection))->replace('role_permissions', 'role_id', 'permission_id', $roleId, $permissionIds);`

### Shared test fixture (task 002 reuses it)
`createRoleSavepointConnection()` is a global function in the `Marko\AdminAuth\Tests\Unit\Repository` namespace. Copying it into another test file under the same name fails with "Cannot redeclare function". Move it into a fixture class `Marko\AdminAuth\Tests\Fixtures\SqliteSavepointConnection` (implements `ConnectionInterface, TransactionInterface`):
- the constructor creates **both** `role_permissions (role_id, permission_id)` and `admin_user_roles (user_id, role_id)` tables
- takes `?array &$txLog` and a generic `?int $failOnId` (throws `RuntimeException` on an INSERT that binds that id)
- RoleRepositoryTest switches to the fixture and removes `createRoleSavepointConnection()`; `rolePermissionIds()` may stay

## Requirements (Test Descriptions)
In PivotSyncTest.php:
- [x] `it deletes the owner's rows and inserts the new set in one multi-row statement`
- [x] `it splits inserts into statements of at most 500 rows` (1001 ids give 3 INSERTs of 500, 500 and 1 rows)
- [x] `it only deletes when given no related ids` (exactly one statement, no INSERT)
- [x] `it runs the delete and inserts in one transaction when the connection supports transactions`
- [x] `it runs the statements directly when the connection does not support transactions`
- [x] existing RoleRepository syncPermissions tests stay green on the new fixture

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Implemented with two deviations from the review's pinned contract:
- No new SqliteSavepointConnection fixture. The existing tests/Fixtures/SqlitePermissionConnection already has real savepoints and a failOn hook, so it gained an admin_user_roles table (with the real (user_id, role_id) unique index) plus roleIdsForUser() and permissionIdsForRole(). RoleRepositoryTest keeps its own helper untouched, so nothing is redeclared.
- replace() takes (table, ownerColumn, ownerId, relatedColumn, relatedIds); both repositories call it with named arguments.
