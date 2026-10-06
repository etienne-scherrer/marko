# Task 004: Integration: role/user pivots, cascades, unique indexes

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Exercise RoleRepository and AdminUserRepository pivots on real servers with tables built from the entities.

## Context
- Related files: packages/admin-auth/src/Repository/RoleRepository.php, AdminUserRepository.php

## Requirements (Test Descriptions)
- [x] `it syncs and reads role permissions`
- [x] `it reads the distinct permissions of several roles`
- [x] `it syncs and reads user roles`
- [x] `it cascades role, permission and user deletes to the pivots`
- [x] `it rejects a duplicate role permission`
- [x] `it rejects a duplicate user role`

## Acceptance Criteria
- Runs on MySQL 8.4, MariaDB 11.8/10.11, PostgreSQL 17

## Implementation Notes
- Tables come from `AdminAuthSchema::create()` (all five from entities after task 003); one test file per driver under `tests/Integration/{MySql,PgSql}`, `integration-services` group, same skip/cleanup pattern as `PermissionRepositoryTest`.
- Cascades: delete through `RoleRepository::delete()`, `PermissionRepository::delete()` and `AdminUserRepository::delete()`, then count rows in `role_permissions` / `admin_user_roles` with quoted identifiers.
- Duplicate role permission: `syncPermissions($roleId, [$id, $id])` throws `Marko\Database\Exceptions\UniqueConstraintViolationException`; it runs in a transaction, so also assert the role's earlier assignments survive.
- Duplicate user role: `syncRoles()` is NOT transactional (its DELETE commits before the failing INSERT); assert only the exception, not atomicity.

The duplicate role permission test also checks the earlier assignments survive (syncPermissions runs in a transaction). Verified on MySQL 8.4, MariaDB 11.8/10.11 and PostgreSQL 17.
