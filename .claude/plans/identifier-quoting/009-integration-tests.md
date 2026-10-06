# Task 009: Real-database integration tests for reserved-word identifiers (MySQL, MariaDB, PostgreSQL)

**Status**: completed
**Depends on**: 003, 004, 006, 007
**Retry count**: 0

## Description
In each driver's `tests/Integration` (group `integration-services`; the MySQL directory also runs against MariaDB in CI), create a table from an entity with `key`, `group` and `order` columns through `SchemaBuilder` + the driver generator, and round-trip it through save/find/findOneBy/findBy/update/delete/exists/insertBatch, plus DataMigration and DatabaseTestHelper. Also prove that DDL with a delimiter in a name runs. PermissionRepository integration coverage is task 011.

## Context
- Related files: `packages/database-mysql/tests/Integration/ReservedWordIdentifiersTest.php`, `packages/database-pgsql/tests/Integration/ReservedWordIdentifiersTest.php`
- Patterns to follow: `SchemaDiffSettlesTest.php`, `GeneratedPrimaryKeysTest.php`; settings from `tests/Fixtures/IntegrationDatabase` (skip without host, fail under `MARKO_INTEGRATION_REQUIRED`)

## Requirements (Test Descriptions)
- [x] `it round-trips an entity with reserved-word columns through save, find, findOneBy, findBy, update, exists and delete`
- [x] `it inserts a batch of entities with reserved-word columns`
- [x] `it writes reserved-word columns through DataMigration and DatabaseTestHelper`
- [x] `it creates and uses a table whose names contain the delimiter`

## Acceptance Criteria
- Pass locally against MySQL 8.4, MariaDB 11.8 and PostgreSQL 17 (CI already runs both driver directories; the MySQL one twice)

## Implementation Notes
- Use table names unique to these files and `DROP TABLE IF EXISTS` (with the driver's quoting) in both `beforeEach` and `afterEach`; the PostgreSQL tests share the `marko_test` database with the other pgsql driver suites.
- The delimiter-name table's cleanup DROP must quote through `MySqlIdentifier`/`PgSqlIdentifier`, or the drop itself fails and leaves the table behind.
- `DatabaseTestHelper` needs `ConnectionInterface&TransactionInterface`; the driver connections satisfy it.
