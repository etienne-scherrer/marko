# Plan: Queue and Session Entity-Owned Tables

## Created
2026-10-06

## Status
completed

## Objective
Ship `#[Table]` entities for `jobs`, `failed_jobs` (marko/queue-database) and `sessions` (marko/session-database) so `marko db:migrate` creates them on PostgreSQL, MySQL and MariaDB, as notification-database, webhook and media already do (issue #337, option A, the issue's recommendation).

## Related Issues
Closes #337

## Discovery Notes
- `db:migrate` runs only the app's `database/migrations/*.php` plus a migration generated from the entity diff (entities discovered in `vendor/*/*/src/Entity`). queue-database's `CreateJobsTable`/`CreateFailedJobsTable` are classes nothing runs; session-database ships only raw SQL in its docs.
- Generated DDL from the new entities matches the documented DDL exactly on both generators (checked: `VARCHAR(36)` PK, `TEXT`, `INT`/`INTEGER`, `TIMESTAMP` with `DEFAULT CURRENT_TIMESTAMP`, `idx_queue_available (queue, available_at)`; `sessions.id VARCHAR(128)`).
- The fixture app (`tests/Integration/App/Fixture`) creates jobs/failed_jobs via the deleted classes (Task 001 inlines them) and sessions with `VARCHAR(255)` (Task 005 aligns it to `VARCHAR(128)`).
- `MigrateCommand` creates entity tables only by generating a migration, in development or with `--generate`. In production it only warns about drift. Tests run it under `APP_ENV=local`, and the docs describe "generate in dev, commit, deploy".
- `TruncateDatabase::tables()` already covers every discovered entity table; shipping the entities is the fix, a real-database test proves it.
- The raw SQL in `DatabaseQueue`, `DatabaseFailedJobRepository` and `DatabaseSessionHandler` stays untouched (#338 quotes it next). #276 UTC timestamps, #224 row locks and #266/#267 validateId/lazy start keep their behaviour; the column types are unchanged.

## Scope

### In Scope
- `DatabaseJob`, `DatabaseFailedJob` (queue-database) and `DatabaseSession` (session-database) entities
- Delete `src/Migration/*` and their tests from queue-database
- queue-database integration tests build tables from the entities (SchemaBuilder + driver generator); legacy-DDL tables diff empty
- session-database real-database tests on MySQL/MariaDB (`ON DUPLICATE KEY`) and PostgreSQL (`ON CONFLICT`)
- A small fixture project proving a fresh `db:migrate` creates the three tables on PostgreSQL, MySQL and MariaDB, and that `queue:work`, the session handler and `TruncateDatabase` work on them
- CI runs the new MySQL suites against both MariaDB versions; docs and READMEs

### Out of Scope
- Changing any raw SQL in the queue/session code (#338)
- Package migration discovery (option B) or publish commands (option C)
- SQLite support (the queue's SqliteConnection test fixture keeps inline DDL)

## Success Criteria
- [x] Fresh `db:migrate` (development, generating) creates jobs, failed_jobs, sessions on PostgreSQL, MySQL 8.4, MariaDB 11.8 and 10.11, and a second run reports nothing to migrate
- [x] Tables created from the old documented DDL produce an empty diff against the entities
- [x] queue worker and database session handler work end to end on the migrated tables
- [x] TruncateDatabase empties the three tables (tested)
- [x] Docs use `marko db:migrate`; no raw session SQL
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Queue entities; remove migration classes; inline fixture jobs/failed_jobs DDL | - | completed |
| 002 | Session entity | - | completed |
| 003 | queue-database integration tests on entity-built tables | 001 | completed |
| 004 | session-database real-database tests | 002 | completed |
| 005 | Fresh db:migrate fixture tests (PostgreSQL, MySQL/MariaDB) | 001, 002 | completed |
| 006 | CI MariaDB steps and testing docs | 003, 004, 005 | completed |
| 007 | Package docs and READMEs | 001, 002 | completed |

## Architecture Notes
- Entities own the schema only (like admin-auth's `RolePermission`); the repositories keep their raw SQL.
- Test helpers create tables via `SchemaBuilder` + `SqlGeneratorInterface::generateUp(new SchemaDiff(tablesToCreate: ...))`, as `AdminAuthSchema` does.
- Local services: `docker compose -p marko337 -f tests/Integration/compose.yml --profile mariadb up -d --wait` with DB_PORT=47432, MYSQL_PORT=43306, MARIADB_PORT=43307, MARIADB_10_11_PORT=43308, REDIS_PORT=46379.

## Risks & Mitigations
- MySQL/MariaDB may report the TIMESTAMP default differently and leave drift. The legacy-DDL diff tests settle expression defaults the way `MigrateCommand::calculateDiff()` does. The second `db:migrate` run and the legacy-DDL diff test assert an empty diff on every server.
- Deleting `CreateJobsTable`/`CreateFailedJobsTable` breaks apps whose own migration instantiates them. The docs carry an upgrade note.
- Shared `marko_test` database between suites: the db:migrate fixture uses its own database per run.
