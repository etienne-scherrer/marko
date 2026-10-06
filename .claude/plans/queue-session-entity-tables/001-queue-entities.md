# Task 001: Queue entities; remove migration classes

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `DatabaseJob` (`jobs`) and `DatabaseFailedJob` (`failed_jobs`) entities to marko/queue-database matching the current DDL, and delete the `CreateJobsTable`/`CreateFailedJobsTable` migration classes nothing runs.

## Context
- Related files: packages/queue-database/src/Migration/*, packages/queue-database/tests/Migration/*, packages/queue-database/tests/Fixtures/SqliteConnection.php, tests/Integration/App/Fixture/database/migrations/2026_01_01_00000{3,4}_*.php
- Patterns to follow: packages/notification-database/src/Entity/DatabaseNotification.php, admin-auth RolePermission

## Requirements (Test Descriptions)
- [ ] `it maps DatabaseJob to the jobs table with the documented columns`
- [ ] `it indexes jobs on queue and available_at as idx_queue_available`
- [ ] `it maps DatabaseFailedJob to the failed_jobs table with the documented columns`
- [ ] `it generates the documented jobs and failed_jobs DDL on MySQL and PostgreSQL`
- [ ] `it ships no migration classes`

## Acceptance Criteria
- All requirements have passing tests
- SqliteConnection keeps inline DDL (SQLite is not a supported driver); it currently calls `new CreateJobsTable()->up()`, so replace that with inline SQL
- The main fixture migrations `tests/Integration/App/Fixture/database/migrations/2026_01_01_000003_create_jobs_table.php` and `..._000004_create_failed_jobs_table.php` (currently `return new CreateJobsTable();`) become anonymous `Migration` classes with the documented DDL inline (copy the deleted classes' SQL verbatim, index included), so the main integration suite never loads a deleted class
- `MySqlRoundTripTest`/`PgSqlRoundTripTest` still reference the deleted classes; Task 003 repoints them. Grep for `Migration\\Create` before finishing; nothing else may reference the classes

## Implementation Notes
