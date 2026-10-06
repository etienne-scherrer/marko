# Task 001: AdminUserRole entity and permissions group index

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add the `AdminUserRole` entity for `admin_user_roles` (mirrors `RolePermission`) and keep the `group` index on `permissions` as an entity `#[Index]`.

## Context
- Related files: packages/admin-auth/src/Entity/RolePermission.php, RolePermissionInterface.php, Permission.php
- Patterns to follow: RolePermission

## Requirements (Test Descriptions)
- [x] `it maps AdminUserRole to the admin_user_roles table with an auto-increment id`
- [x] `it references admin_users and roles with cascading deletes`
- [x] `it declares a unique index on user_id and role_id`
- [x] `it exposes the user and role ids through AdminUserRoleInterface`
- [x] `it indexes the permissions group column`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes

Also restored the old migrations secondary FK indexes as entity indexes (`idx_admin_user_roles_role_id`, `idx_role_permissions_permission_id`): PostgreSQL does not index FK columns, so cascading deletes would scan the pivots, and matching names keep the upgrade diff for hand-made tables non-destructive.
