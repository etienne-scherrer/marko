# Task 003: queue-database integration tests on entity-built tables

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Point `MySqlRoundTripTest` and `PgSqlRoundTripTest` at tables built from the entities through SchemaBuilder and the driver generator, and prove that tables created from the old documented DDL diff empty against the entities.

## Context
- Related files: packages/queue-database/tests/Integration/*, new tests/Fixtures/QueueTables.php
- Patterns to follow: packages/admin-auth/tests/Integration/AdminAuthSchema.php (create), packages/database-{mysql,pgsql}/tests/Integration/SchemaDiffSettlesTest.php (diff)
- The round-trip tests' setup currently calls the `CreateJobsTable`/`CreateFailedJobsTable` classes Task 001 deletes; replace those calls (and the `use` lines)
- Legacy-DDL diff: create the tables with the documented DDL inline in the test, introspect, settle expression defaults (`ExpressionDefaultCanonicalizer`, as `MigrateCommand::calculateDiff()` does) and run `DiffCalculator` against the two entities' metadata only. Without the settle step, MariaDB's `current_timestamp()` spelling reports false drift on `created_at`/`failed_at`. Unrelated tables in the shared `marko_test` database must not enter the diff

## Requirements (Test Descriptions)
- [ ] `it builds jobs and failed_jobs from the entities on MySQL`
- [ ] `it builds jobs and failed_jobs from the entities on PostgreSQL`
- [ ] `it diffs tables created from the documented DDL as empty on MySQL`
- [ ] `it diffs tables created from the documented DDL as empty on PostgreSQL`

## Acceptance Criteria
- Existing round-trip cases pass unchanged against entity-built tables

## Implementation Notes
