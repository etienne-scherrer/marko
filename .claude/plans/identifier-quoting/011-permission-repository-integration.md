# Task 011: PermissionRepository integration tests on MySQL, MariaDB and PostgreSQL

**Status**: pending
**Depends on**: 003, 004, 006, 008
**Retry count**: 0

## Description
Prove `PermissionRepository` findByKey/findByGroup/save/syncFromRegistry on real servers. The tests live in `marko/admin-auth` (the driver packages must not dev-depend on admin-auth, which pulls admin/authentication/routing), and CI's MariaDB step is extended so the MySQL variant also runs against MariaDB.

## Context
- New files: `packages/admin-auth/tests/Integration/MySql/PermissionRepositoryTest.php`, `packages/admin-auth/tests/Integration/PgSql/PermissionRepositoryTest.php` (group `integration-services`)
- `packages/admin-auth/composer.json`: add `marko/database-mysql` and `marko/database-pgsql` (`self.version`) to `require-dev`
- `.github/workflows/ci.yml`: the "Run MySQL driver integration suite against MariaDB" step also runs `packages/admin-auth/tests/Integration/MySql`; update the comment above it ("Only the MySQL driver suite runs twice")
- Connection settings: reuse `Marko\Database\MySql\Tests\Fixtures\IntegrationDatabase` / `Marko\Database\PgSql\Tests\Fixtures\IntegrationDatabase` (autoloaded via the root `autoload-dev`) so skip/required semantics match
- `admin-auth` migrations are MySQL-only DDL; create `permissions` from the `Permission` entity via `SchemaBuilder` + the driver generator on both servers

## Requirements (Test Descriptions)
- [ ] `it saves permissions and finds them by key and by group` (MySQL/MariaDB and PostgreSQL)
- [ ] `it syncs permissions from the registry without duplicating existing ones` (MySQL/MariaDB and PostgreSQL)

## Acceptance Criteria
- Pass locally against MySQL 8.4, MariaDB 11.8 and PostgreSQL 17
- `composer test:integration` picks them up; the MariaDB CI step runs the MySQL variant

## Implementation Notes
- `permissions` is a fixed table name: `DROP TABLE IF EXISTS` (quoted) in both `beforeEach` and `afterEach`.
- Keep MySQL and PgSql variants in separate directories so the MariaDB CI step doesn't also re-run the PostgreSQL file (the job env sets `MARKO_TEST_PGSQL_*` for every step).
