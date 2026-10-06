# Task 006: Real-driver integration tests (pgsql, mysql)

**Status**: completed
**Depends on**: 004, 005
**Retry count**: 0

## Description
Env-gated tests against real servers through the real module wiring and repositories.

## Context
- Related files: packages/database-{pgsql,mysql}/tests/Integration/, tests/Fixtures/SharedConnection/
- Pattern: SharedConnectionTransactionTest (skips without MARKO_TEST_*_HOST)
- **Do not redeclare helpers.** `SharedConnectionTransactionTest.php` already declares `pgsqlIntegrationConfig()`, `pgsqlRowCount()` and `PGSQL_SKIP_REASON` (MySQL: `mysqlIntegrationConfig()`, `mysqlRowCount()`, `MYSQL_SKIP_REASON`) in the `...\Tests\Integration` namespace. A new file in that namespace that declares them again fatals the suite. Use distinct names, or move the shared helpers into one file that both tests require.
- **Use dedicated tables** (e.g. `constraint_users` with a unique `email` and `constraint_posts` with an FK to it). Do not reuse `shared_accounts`/`shared_audit_entries`, because the existing file drops and recreates them and parallel workers would race. Drop the child table before the parent.
- New fixture entities and repositories go in a separate fixture directory (e.g. `tests/Fixtures/Constraint/`), and the FK must be real (`REFERENCES`, InnoDB on MySQL).
- PostgreSQL aborts the surrounding transaction after a violation: later statements fail with 25P02 until rollback. Trigger violations outside a transaction (or roll back first) before asserting through the same connection.
- MySQL is not in `tests/Integration/compose.yml` or CI, so these tests only run where a developer provides a server. Record in Implementation Notes which servers and versions were actually exercised.

## Requirements (Test Descriptions)
- [x] `it throws a unique violation naming the constraint when saving a duplicate`
- [x] `it throws a foreign key violation when deleting a referenced row`
- [x] `it keeps the original PDOException as the previous exception`
- [x] `it does not leak the duplicate value into the message`
- [x] `it rethrows the typed violation from insertBatch with the PDOException as previous`

## Acceptance Criteria
- Tests pass against real PostgreSQL 17 and MySQL 8 when available; skip cleanly without services
- Full `composer test` (parallel) still passes alongside SharedConnectionTransactionTest

## Implementation Notes
- Exercised against PostgreSQL 17 (postgres:17) and MySQL 8.4 (mysql:8.4) in local containers: all 8 cases per driver pass (unique, FK on delete, FK on insert, not-null, check, previous PDOException, no value leak, insertBatch).
- Fixtures live in tests/Fixtures/Constraint/ with dedicated constraint_users/constraint_posts tables; helpers use distinct names (pgsqlConstraintConfig(), mysqlConstraintConfig()).
