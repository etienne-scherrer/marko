# Task 002: Real-database search tests

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
Search a table built from an entity (through SchemaBuilder and the driver generator) with key, group and order columns on PostgreSQL, MySQL 8.4 and MariaDB, plus a mixed-case displayName column on PostgreSQL. Wire the MySQL suite into the MariaDB CI steps.

## Context
- Related files: packages/search/tests/Integration/, .github/workflows/ci.yml, tests/CiWorkflowTest.php, packages/search/composer.json
- Patterns to follow: packages/session-database/tests/Integration/{MySql,PgSql}/DatabaseSessionHandlerTest.php (IntegrationDatabase fixture skip/require, `pest()->group('integration-services')`, table built through the driver generator in beforeEach, dropped in afterEach), #331

## Constraints (from review)
- Layout: `packages/search/tests/Integration/PgSql/` and `packages/search/tests/Integration/MySql/` (shared entity/table fixture under `packages/search/tests/Fixtures/`). Add `packages/search/tests/Integration/MySql` to **both** MariaDB steps in .github/workflows/ci.yml (11.8 and 10.11) and to the path list in tests/CiWorkflowTest.php.
- PostgreSQL rejects `LIKE` on integer columns: the searchable `key`/`group` columns (and `displayName`) must be string columns. An integer `order` column can be sorted or filtered on, but must not be returned from `getSearchableFields()`.
- On PostgreSQL, assert the result row key is exactly `displayName` (proves the column was not case-folded).
- `marko/search` composer.json already has `marko/database` in require and `marko/database-mysql`/`-pgsql` in require-dev; check this, don't add duplicates.

## Requirements (Test Descriptions)
- [ ] `it searches reserved-word searchable columns`
- [ ] `it filters on a reserved-word column`
- [ ] `it sorts by a reserved-word column`
- [ ] `it searches, filters and sorts a mixed-case column (PostgreSQL)`
- [ ] `it runs the search MySQL suite against both MariaDB services (CiWorkflowTest)`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
