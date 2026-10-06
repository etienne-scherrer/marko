# Plan: Integration Test Suite with Real Drivers

## Created
2026-10-05

## Status
completed

## Objective
Add a service-backed integration suite that boots a real fixture application through `Application::boot()` with real
drivers (Postgres, Redis, database queue, database sessions), runs in its own CI job, and records every known-broken
cross-package behaviour as a `todo` test owned by its ticket.

## Related Issues
Closes #187

## Discovery Notes
- `packages/roadrunner/tests/Fixtures/app` is the only real-app fixture; it commits hand-written `vendor/marko/*`
  stub manifests and borrows the monorepo autoloader through `vendor/autoload.php`.
- `ModuleDiscovery` scans `vendor/*/*` with `scandir`/`is_dir`, so symlinks to `packages/*` work and run each
  package's real `module.php` wiring. `DependencyResolver` does not fail on missing `require` targets.
- `DatabaseConfig` reads `config/database.php` straight from `ProjectPaths`; the Migrator only reads
  `database/migrations/*.php` at the project root, and queue-database ships `CreateJobsTable`/`CreateFailedJobsTable`
  classes that apps wire into their own migration files.
- `db:migrate` in development mode diffs entities against the live schema and generates migrations into the project;
  tables not owned by an entity (jobs, failed_jobs, sessions) are not excluded from that diff, so the suite runs
  `db:migrate --no-generate` with committed migrations and leaves generation to #170.
- `errors-simple` registers global error/exception handlers in its boot callback; the harness restores PHPUnit's
  handlers after boot.
- cache-redis ignores config today and always connects to `127.0.0.1:6379` db 0 (#166).
- `tests/` is not linted or analysed by PHPStan; `tests/CiWorkflowTest.php` asserts the workflow's structure.

## Scope

### In Scope
- Fixture app at `tests/Integration/App/Fixture` (app module, config, migrations), copied into a fresh temp project per
  test with `vendor/marko/*` symlinked to the monorepo packages
- Harness helpers (`tests/Integration/App/Helpers.php`): skip-with-reason helper, required mode for CI, per-process
  Postgres database reset, boot, command runner, request helper
- `integration-services` group, `composer test:integration`, `tests/Integration/compose.yml`
- Today-passing cases; `todo` cases for every ticket in the #187 table
- "Integration" CI job with Postgres 17 and Redis 7 services; `tests/CiWorkflowTest.php` updated
- `.claude/testing.md` section

### Out of Scope
- Fixing any framework behaviour (each todo is owned by its ticket)
- MySQL cases (deferred until a MySQL-specific ticket needs them)
- Making the Integration job a required check (repo setting; mentioned in PR)

## Success Criteria
- [x] Fixture app boots via `Application::boot()` with the module set from #187
- [x] `composer test` green with no services (integration cases skipped, not failed)
- [x] `composer test:integration` green locally against compose services
- [x] CI Integration job green
- [x] Every #187 table row present as a todo naming its ticket
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Harness helpers, skip reason, compose file, composer script | - | completed |
| 002 | Fixture application (module, config, migrations) and boot case | 001 | completed |
| 003 | Database, cache, HTTP and queue cases that pass today | 002 | completed |
| 004 | Todo cases for every owning ticket | 002 | completed |
| 005 | Integration CI job and CiWorkflowTest | 001 | completed |
| 006 | Contributor docs | 003, 004, 005 | completed |

## Architecture Notes
- Each test boots a fresh copy of the fixture in `sys_get_temp_dir()` so generated files, sessions and caches never
  touch the repository; `vendor/` is generated (symlinks + autoload shim) rather than committed.
- Per-process database name (`marko_integration` + `_<TEST_TOKEN>`) keeps `--parallel` runs isolated.
- `MARKO_INTEGRATION_REQUIRED=1` (set in CI) turns "services unreachable" into a failure so the job can never go green
  by skipping everything.

## Risks & Mitigations
- Local port clashes: compose ports are overridable (`DB_PORT`, `REDIS_PORT`); cache-redis is pinned to 6379 until #166.
- Global error handlers from errors-simple: restored after boot.
- Redis shared across parallel workers: tests use unique keys; no FLUSHDB.
- Driver binding conflicts: only the #187 module set is linked into the fixture `vendor/marko/*` (constant in 001).
- Symlinked vendor: cleanup unlinks the links and never follows them into `packages/*`.
- Lingering PDO connections (`ConnectionInterface` is not a singleton): the reset uses `DROP DATABASE ... WITH (FORCE)`.
- See `_devils_advocate.md` for the full review.
