# Task 010: Driver integration tests (pgsql + mysql)

**Status**: pending
**Depends on**: [002, 003, 007, 008, 009]
**Retry count**: 0

## Description
Real-server tests in packages/database-{pgsql,mysql}/tests/Integration that cover nesting, callbacks, locks (from a second connection) and upsert.

## Context
- Related files: packages/database-*/tests/Integration/SharedConnectionTransactionTest.php (pattern for tests gated on environment variables)

## Requirements (Test Descriptions)
- [ ] `it commits nested transaction() calls together`
- [ ] `it rolls back only the inner work when an inner failure is caught`
- [ ] `it rolls back everything when the outer transaction fails`
- [ ] `it runs after-commit callbacks after the outermost commit and not after rollback`
- [ ] `it lets a second connection skip a row locked with lockForUpdate`
- [ ] `it makes a second connection fail fast with noWait on a locked row`
- [ ] `it upserts new rows, existing rows and mixed batches`
- [ ] `it rejects (pgsql) a batch that hits the same conflict key twice`

Notes:
- Pest loads every test file into one process. Reuse the `pgsqlIntegrationConfig()` / `mysql...` helpers and the `*_SKIP_REASON` consts from `SharedConnectionTransactionTest.php`, or give new global functions and consts unique names, or the run fatals with "Cannot redeclare".
- Create and drop tables outside the transactions under test. MySQL DDL issues an implicit commit.
- The upsert table needs a UNIQUE constraint that matches `uniqueBy`, which PostgreSQL `ON CONFLICT` requires.
- Assert table contents, not the same affected-row count on both drivers. MySQL returns 2 per updated row.
- The second (locking-contender) connection must be a separate driver instance inside its own `beginTransaction()`, or the builder's outside-transaction check throws first. A NOWAIT failure surfaces as a `PDOException` (PostgreSQL SQLSTATE 55P03, MySQL 3572) unless #177 has landed.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
