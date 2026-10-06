# Task 007: Real-database tests (PostgreSQL, MySQL, MariaDB)

**Status**: completed
**Depends on**: 003, 004
**Retry count**: 0

## Description
Extend `tests/Integration/{PgSql,MySql}/PermissionRepositoryTest.php` to create the admin-auth tables from the entities, and cover sync update/report and prune. CI runs the MySql file against MySQL 8.4 and MariaDB 11.8.

## Context
- Related files: `packages/admin-auth/tests/Integration/*`, `AdminUserProvider.php`

## Requirements (Test Descriptions)
- [x] `it updates labels and groups and reports unregistered permissions with role counts`
- [x] `it prunes unregistered permissions and their role assignments but keeps wildcard grants`
- [x] `it no longer grants a pruned key to a user loaded through AdminUserProvider`

## Acceptance Criteria
- Passing against local PostgreSQL 17, MySQL 8.4 and MariaDB 11.8

## Implementation Notes
- `admin_user_roles` has no entity, and the migrations in `database/migrations/` are MySQL-only DDL (`INT UNSIGNED`, inline `INDEX`), so neither works for PostgreSQL. Build `roles`, `permissions`, `role_permissions`, `admin_users` from their entities with SchemaBuilder plus the driver generator, in FK order (roles and permissions first). Create `admin_user_roles (user_id, role_id)` with portable raw DDL, with identifiers quoted through `quoteIdentifier()`.
- In both `beforeEach` and `afterEach`, drop in reverse dependency order: admin_user_roles, role_permissions, admin_users, roles, permissions. The current `beforeEach` drops only `permissions`, which fails on both engines once `role_permissions` references it (a leftover from a crashed run).
- The existing `syncs permissions from the registry without duplicating existing ones` test keeps passing because it ignores the return value. Leave it in place.
