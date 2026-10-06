# Task 003: Integration: schema from entities, fresh db:migrate, admin login

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Build all five tables from entities in `AdminAuthSchema`, and prove on MySQL/MariaDB and PostgreSQL that the real `db:migrate` on an empty schema creates every admin-auth table, settles, and that an admin user can log in with roles and permissions loaded.

## Context
- Related files: packages/admin-auth/tests/Integration/AdminAuthSchema.php, tests/Integration/{MySql,PgSql}/
- Patterns to follow: PermissionRepositoryTest, database-mysql SchemaDiffSettlesTest

## Requirements (Test Descriptions)
- [x] `it creates every admin-auth table from the entities on a fresh db:migrate`
- [x] `it reports nothing to migrate on a second run`
- [x] `it logs in an admin user and loads roles and permissions`

## Acceptance Criteria
- Runs on MySQL 8.4, MariaDB 11.8/10.11, PostgreSQL 17

## Implementation Notes
- `AdminAuthSchema::ENTITIES` gains `AdminUserRole::class` after `Role` and `AdminUser` (FK order); drop the raw-SQL `admin_user_roles` CREATE. `TABLES` drop order already puts `admin_user_roles` first. Existing `{MySql,PgSql}/PermissionRepositoryTest` must still pass on the rebuilt helper.
- Shared harness lives in `tests/Integration` (not duplicated per driver): temp project with only `vendor/marko/admin-auth` symlinked to the package (so `EntityDiscovery::discoverInVendor` sees only admin-auth entities) and an empty `database/migrations`; `MigrateCommand` constructed by hand with the real Migrator, DataMigrator, MigrationGenerator, EntityDiscovery, SchemaRegistry, DiffCalculator, driver generator and `ProjectPaths($project)`.
- Force generation deterministically: `new AppEnvironment(['APP_ENV' => 'local'])` or `--generate`; never depend on the developer's shell `APP_ENV`.
- Scoped introspector: decorator over the real driver introspector whose `getTables()` returns only the five admin-auth tables plus `migrations`; everything else delegates. Without it, other suites' tables in `marko_test` appear as tables to drop (destructive → command refuses non-interactively).
- `migrations` is shared in `marko_test`: drop it in beforeEach and afterEach, as database-pgsql `ColumnCastsAndExpressionDefaultsTest` does; also remove the temp project.
- The login test must run on the schema `db:migrate` created (not `AdminAuthSchema::create()`), with a real hasher, through `AdminUserProvider::retrieveByCredentials()` + `validateCredentials()`, asserting roles and permission keys.
- Files are `pest()->group('integration-services')` and skip via the driver `IntegrationDatabase::config()` fixture like `PermissionRepositoryTest`; the MySql directory is rerun against MariaDB 11.8 and 10.11 in CI.
