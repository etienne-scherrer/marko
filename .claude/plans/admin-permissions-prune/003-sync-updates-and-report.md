# Task 003: syncFromRegistry updates and reports unregistered rows

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`syncFromRegistry()` returns `PermissionSyncResult`. It reads the table once, inserts missing keys, updates `label`/`group` on rows whose values changed, and reports rows the registry doesn't know: keys without `*` as `UnregisteredPermission` with the number of roles holding them, keys with `*` as wildcard grants kept. It deletes nothing. Add `findUnregistered(registry): list<UnregisteredPermission>`. Writes run in one transaction when supported.

## Context
- Related files: `PermissionRepository.php`, `PermissionRepositoryInterface.php`, `tests/Unit/Repository/PermissionRepositoryTest.php`, `tests/Unit/Repository/PermissionRepositoryInterfaceTest.php`

## Requirements (Test Descriptions)
- [x] `it inserts registered permissions missing from the table`
- [x] `it updates the label and group of an existing permission that changed`
- [x] `it leaves an unchanged permission alone`
- [x] `it reports unregistered permissions with the number of roles holding each`
- [x] `it reports wildcard keys as kept rather than unregistered`
- [x] `it deletes nothing when syncing`
- [x] `it declares syncFromRegistry returning PermissionSyncResult on the interface`
- [x] `it declares findUnregistered on the interface`
- [x] `it counts each role once when role_permissions holds duplicate rows`

## Acceptance Criteria
- All requirements have passing tests
- The whole admin-auth suite is green after this task. Changing the return type breaks existing code, and this task owns the fixes:
  - `SyncPermissionsCommand::execute()` does `$registered - $created` (TypeError on an object). Adapt it minimally to `$result->createdCount()` and keep the current output line. Task 006 rewrites the command.
  - `SyncPermissionsCommandTest` uses `willReturn(1)`/`willReturn(2)`. Return a `PermissionSyncResult` instead.
  - `PermissionRepositoryTest`: replace `returns the number of permissions created by syncFromRegistry` and `syncs permissions ... creating new and preserving existing` (asserts two per-key SELECTs, which contradicts reading the table once). Move the sync tests onto the SQLite fixture from task 002 and drop `createPermissionSyncMockConnection` if nothing else uses it.
  - `PermissionRepositoryInterfaceTest` (line ~70 asserts the int return, line ~23 lists the methods): update it.

## Implementation Notes
- Role counts use `COUNT(DISTINCT role_id)`, because the entity-built `role_permissions` table (task 007) has no unique index.
- Keep the existing `contains no driver-specific identifier quoting` test green: no backticks, and quote through `quoteIdentifier()`.
- The transaction fallback copies `RoleRepository::syncPermissions()`: use `transaction()` when the connection is a `TransactionInterface`, otherwise run directly.
