# Task 002: Sync result value objects and SQLite test connection

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `UnregisteredPermission` (id, key, label, group, roleCount) and `PermissionSyncResult` (registeredCount, created keys, updated keys, unregistered list, wildcard keys kept) as readonly value objects in `Marko\AdminAuth\Repository`. Add a shared in-memory SQLite `ConnectionInterface&TransactionInterface` test fixture with the permissions/role_permissions tables, for repository unit tests.

## Context
- Related files: `packages/admin-auth/src/Repository/`, `packages/admin-auth/tests/Fixtures/` (new), pattern from the SQLite double in `tests/Unit/Repository/RoleRepositoryTest.php`

## Requirements (Test Descriptions)
- [x] `it exposes the created, updated, unregistered and wildcard keys of a sync`
- [x] `it counts created, updated and unregistered permissions`
- [x] `it exposes the key, label, group and role count of an unregistered permission`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Contract (tasks 003, 004 and 006 build against it, so use these exact names):
  - `readonly class UnregisteredPermission { __construct(public int $id, public string $key, public string $label, public string $group, public int $roleCount) }`
  - `readonly class PermissionSyncResult { __construct(public int $registeredCount, public array $created /* list<string> */, public array $updated /* list<string> */, public array $unregistered /* list<UnregisteredPermission> */, public array $wildcardKeys /* list<string> */) }` plus `createdCount(): int`, `updatedCount(): int`, `unregisteredCount(): int`
- The fixture is an autoloaded class, not a global function (Pest loads every test file into one process, and `RoleRepositoryTest` already declares global helpers). Use `Marko\AdminAuth\Tests\Fixtures\SqlitePermissionConnection implements ConnectionInterface, TransactionInterface`. It creates `permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, "key" TEXT UNIQUE NOT NULL, label TEXT NOT NULL, "group" TEXT NOT NULL, created_at TEXT NULL)` and `role_permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL)`. It has real nested transactions (savepoints, as in `createRoleSavepointConnection`), records executed SQL, and exposes a failure-injection hook (e.g. `failOn(string $sqlFragment)`) so task 004 can test rollback. SQLite leaves FKs off by default, which is what task 004 needs to prove the explicit role_permissions delete.
