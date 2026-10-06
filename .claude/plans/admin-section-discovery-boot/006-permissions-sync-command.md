# Task 006: admin-auth:permissions:sync command

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Persisting permissions is explicit, never at boot. `syncFromRegistry()` returns how many permissions it created, and the `admin-auth:permissions:sync` command calls it with the shared registry and reports the result.

## Context
- Related files: packages/admin-auth/src/Repository/PermissionRepository.php, PermissionRepositoryInterface.php, packages/authentication/src/Command/ClearTokensCommand.php (pattern)
- Signature change: `syncFromRegistry(PermissionRegistryInterface $registry): int` on both the interface and the implementation.
- Existing test packages/admin-auth/tests/Unit/Repository/PermissionRepositoryInterfaceTest.php:70 (`syncFromRegistry method signature returns void`) must be updated to assert `int`; PermissionRepositoryTest's existing sync tests must still pass.
- Command class: `Marko\AdminAuth\Command\SyncPermissionsCommand` in packages/admin-auth/src/Command/, `#[Command(name: 'admin-auth:permissions:sync', ...)]`, injecting `PermissionRegistryInterface` and `PermissionRepositoryInterface`. Tests use a fake/mocked repository (no real database).

## Requirements (Test Descriptions)
- [x] `it returns the number of permissions created by syncFromRegistry`
- [x] `it syncs the registered permissions to the database`
- [x] `it reports how many permissions were created and how many are registered`
- [x] `it is registered as the admin-auth:permissions:sync command`

## Acceptance Criteria
- Command exits 0

## Implementation Notes
