# Task 004: pruneUnregistered deletes stale rows and role assignments

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Add `pruneUnregistered(PermissionRegistryInterface $registry): list<UnregisteredPermission>`. Inside one `transaction()` it finds the unregistered non-wildcard rows, deletes their `role_permissions` rows explicitly, then the `permissions` rows, and returns what it removed. Keys containing `*` are never deleted.

## Context
- Related files: `PermissionRepository.php`, `PermissionRepositoryInterface.php`, `RolePermission.php` (CASCADE FK not relied upon)

## Requirements (Test Descriptions)
- [x] `it deletes unregistered permissions and their role assignments`
- [x] `it never deletes a key containing a wildcard`
- [x] `it keeps registered permissions and their role assignments`
- [x] `it returns the permissions it removed with their role counts`
- [x] `it runs the deletes in one transaction and rolls back on failure`
- [x] `it issues no delete when nothing is unregistered`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Use the SQLite fixture from task 002. Its failure-injection hook drives the rollback test. Its FKs are off, which proves the explicit `role_permissions` delete.
- Chunk the `IN (...)` deletes at 500 ids. Role counts use `COUNT(DISTINCT role_id)`, as in task 003.
- Raw-SQL deletes fire no `EntityDeleting`/`EntityDeleted` events. Say so in the docblock.
- Add a `it declares pruneUnregistered on the interface` assertion to `PermissionRepositoryInterfaceTest`.
