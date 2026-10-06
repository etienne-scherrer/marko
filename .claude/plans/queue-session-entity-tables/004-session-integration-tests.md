# Task 004: session-database real-database tests

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Add `integration-services` tests that run `DatabaseSessionHandler` against MySQL/MariaDB (`ON DUPLICATE KEY UPDATE`) and PostgreSQL (`ON CONFLICT`) on a sessions table built from the entity.

## Context
- Related files: packages/session-database/tests/Integration/{MySql,PgSql}, packages/session-database/composer.json (require-dev drivers)
- Patterns to follow: database-mysql/database-pgsql IntegrationDatabase fixtures; packages/queue-database/tests/Integration/MySqlRoundTripTest.php for env vars and skip/require behaviour (`MARKO_TEST_MYSQL_*`, fail instead of skip under `MARKO_INTEGRATION_REQUIRED`); packages/database-{mysql,pgsql}/tests/Integration/SchemaDiffSettlesTest.php for the diff
- MySQL cases live under `tests/Integration/MySql/` and PostgreSQL cases under `tests/Integration/PgSql/`, so CI can run the MySQL directory alone against MariaDB (Task 006)
- Add `marko/database-mysql` and `marko/database-pgsql` to session-database `require-dev` (it has neither today)
- The legacy-DDL diff settles expression defaults and diffs only the `sessions` table (same approach as Task 003)

## Requirements (Test Descriptions)
- [ ] `it writes, reads and overwrites a session through the upsert`
- [ ] `it validates only a known session within the lifetime`
- [ ] `it refreshes last activity without rewriting the payload or inserting`
- [ ] `it destroys a session and garbage-collects expired rows`
- [ ] `it diffs a sessions table created from the documented DDL as empty`

## Acceptance Criteria
- Each case runs on PostgreSQL and on MySQL (and MariaDB in CI)

## Implementation Notes
