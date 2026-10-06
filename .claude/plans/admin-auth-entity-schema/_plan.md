# Plan: Admin-auth schema from entities

## Created
2026-10-06

## Status
completed

## Objective
Make the admin-auth entities the single source of the package schema so a fresh `db:migrate` creates all five admin-auth tables on MySQL, MariaDB and PostgreSQL, and remove the hand-written MySQL migrations that never run.

## Related Issues
Closes #336

## Discovery Notes
- `Migrator` only reads `{base}/database/migrations`; `packages/admin-auth/database/migrations/*` is never discovered. The only consumer is `tests/Migration/MigrationTest.php` (SQL-string assertions).
- `db:migrate` creates tables from the entity diff (`EntityDiscovery::discoverInVendor` scans `vendor/*/*/src/Entity`). `roles`, `permissions`, `role_permissions`, `admin_users` have entities; `admin_user_roles` does not, so the first admin user load fails.
- `tests/Integration/AdminAuthSchema.php` (from #331/#330) already builds four tables from entities and creates `admin_user_roles` with raw SQL; the MySQL/PgSql `PermissionRepositoryTest`s use it. CI runs `packages/admin-auth/tests/Integration/MySql` against MariaDB 11.8 and 10.11 as well.
- `PermissionRepository::findByGroup()` filters on `group`; the dead migration had `idx_permissions_group`. Decision: keep it as `#[Index]` on `Permission`.

## Scope

### In Scope
- `AdminUserRole` entity + `AdminUserRoleInterface`
- `#[Index('idx_permissions_group', ['group'])]` on `Permission`
- Delete `database/migrations/` and `tests/Migration/MigrationTest.php`
- Unit test: every table in admin-auth raw SQL is owned by an entity
- Integration tests (MySQL/MariaDB, PostgreSQL): real `MigrateCommand` on an empty schema creates all five tables and settles; admin login loads roles and permissions; repository pivots, FK cascades, unique pivots
- Docs page + README

### Out of Scope
- Package migration path discovery in `Migrator`
- Moving `AdminUserRepository` raw SQL to the query builder (#338)

## Success Criteria
- [x] Fresh `db:migrate` creates all five admin-auth tables on MySQL, MariaDB and PostgreSQL
- [x] Dead migrations and `MigrationTest` removed
- [x] Integration tests cover pivots, cascades, unique indexes
- [x] Entity-ownership unit test
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | AdminUserRole entity and permissions group index | - | completed |
| 002 | Entity-ownership unit test; delete dead migrations | 001 | completed |
| 003 | Integration: schema helper from entities, fresh db:migrate, login | 001 | completed |
| 004 | Integration: role/user pivots, cascades, unique indexes | 003 | completed |
| 005 | Docs page and README | 001, 002, 003, 004 | completed |

## Architecture Notes
- The fresh-migrate test runs the real `MigrateCommand` against a temp project whose `vendor/marko/admin-auth` links to the package, so discovery, generation and the `Migrator` are the production path. `marko_test` is shared with the driver suites, so a test-only introspector limits the diff to the admin-auth tables (and `migrations`).
- marko/admin-auth `require` stays driver-free.
- Pre-existing work: the worktree already contains `AdminUserRole{,Interface}`, `AdminUserRoleTest`, `EntitySchemaOwnershipTest`, `AdminAuthTablesIntrospector`, `NativePasswordHasher`, `{MySql,PgSql}/FreshMigrateTest` and an extended `AdminAuthSchema`, and `database/migrations/` is already deleted. Workers start from these files: verify each requirement against them, fill gaps (e.g. the `Permission` group `#[Index]` is still missing), and never recreate or duplicate them.

## Risks & Mitigations
- Shared test database with other suites: tests drop only their own tables; the scoped introspector keeps unrelated tables out of the diff.
- MariaDB `RETURNING` (#339) merging concurrently: rebase before PR and rerun MariaDB.
