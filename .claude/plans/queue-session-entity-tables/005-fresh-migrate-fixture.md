# Task 005: Fresh db:migrate fixture tests

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Boot a small fixture project (queue-database + session-database + one driver) through `Application::boot()` against a fresh database and run the real `db:migrate`. Prove that it:
- creates jobs, failed_jobs and sessions
- has nothing to migrate on a second run
- lets `queue:work` process and fail jobs
- lets the session handler round-trip
- lets `TruncateDatabase` empty the three tables

Also align the main fixture's sessions migration with the documented schema. The jobs/failed_jobs fixture migrations are already inlined by Task 001.

## Context
- Related files: tests/Integration/App/Helpers.php, tests/Integration/App/Fixture/database/migrations/2026_01_01_000005_create_sessions_table.php (`id VARCHAR(255)` becomes `VARCHAR(128)`), new tests/Integration/App/QueueSessionFixture/
- Patterns to follow: tests/Integration/App/MigrationSafetyTest.php (APP_ENV-driven generation), tests/Integration/App/TruncateDatabaseTest.php, tests/Integration/App/QueueTest.php

## Requirements (Test Descriptions)
- [ ] `it creates jobs, failed_jobs and sessions on a fresh database with db:migrate`
- [ ] `it has nothing to migrate on a second db:migrate`
- [ ] `it runs a queued job and records a failing one through queue:work`
- [ ] `it stores, resumes and destroys a session through the database handler`
- [ ] `it empties jobs, failed_jobs and sessions with TruncateDatabase`
- [ ] each of the above on PostgreSQL and on MySQL/MariaDB

## Acceptance Criteria
- The main fixture app suite still passes. Its committed migrations report no drift for jobs, failed_jobs and sessions.
- **Generation is required.** `db:migrate` only creates entity tables when it generates a migration: in development, or with `--generate`. In production it only warns. `bootIntegrationApp()` always passes `--no-generate`, so do not use it here:
  - boot with `migrate: false`
  - run `db:migrate` inside `withIntegrationAppEnv('local', ...)`
  - assert the first run's output contains `Generated:`
  - assert the second run prints `Nothing to migrate.` and writes no new migration file
- **TruncateDatabase.** Its constructor takes a `TestDatabase`, and it refuses outside the `testing`/`test` environments. Wrap the booted app with `new TestDatabase($app)` and call `truncate()` inside `withIntegrationAppEnv('testing', ...)`. Assert that `tables()` contains `failed_jobs`, `jobs` and `sessions`.
- **MySQL/MariaDB harness.** Helpers.php is Postgres-only today. Add:
  - a module list per driver that installs `database-mysql` or `database-pgsql`, never both
  - a fixture `config/database.php` that reads driver and connection settings from env
  - a MySQL create/drop-database helper using `MARKO_TEST_MYSQL_*`, with its own database name suffixed by `TEST_TOKEN` (never the shared `marko_test`)
  - for the MySQL file: skip when `MARKO_TEST_MYSQL_HOST` is unset, and fail instead of skipping under `MARKO_INTEGRATION_REQUIRED`
- **File layout.** Put the MySQL cases in their own test file so CI can run that file alone against each MariaDB (Task 006). The fixture needs its own job classes (one succeeding, one always failing), plus queue config (database driver, `encryption.key`) and session config (database driver).

## Implementation Notes
