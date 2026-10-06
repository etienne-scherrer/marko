# Devil's Advocate Review: queue-session-entity-tables

## Critical (Must fix before building)

1. **Task 005, 007: `db:migrate` creates entity tables only when it generates.** `MigrateCommand` applies pending files first, then generates a migration from the entity diff only in development or with `--generate`. Everywhere else it just prints "Warning: Entity schema differs". `bootIntegrationApp()` always passes `--no-generate`. So a test that "runs the real db:migrate" through the default helper creates nothing, and docs that say "run `marko db:migrate`" are wrong for production deploys. Fix: Task 005 boots with `migrate: false` and runs `db:migrate` under `withIntegrationAppEnv('local', ...)`, which writes a generated file into the temp project. The second run must print "Nothing to migrate." and generate no file. Task 007 documents the real flow: run `marko db:migrate` in development, commit the generated migration, deploy.

2. **Task 005: no MySQL/MariaDB app-fixture harness exists.** `tests/Integration/App/Helpers.php` only knows Postgres. `resetIntegrationDatabase()` opens a `pgsql:` PDO, `integrationServicesProblem()` checks Postgres and Redis, and `INTEGRATION_MODULES` installs `database-pgsql`. The task gives no plan for driver selection, a database per run on MySQL, or skip/require behaviour. Fix: spell these out in Task 005:
   - a module list per driver (database-mysql **or** database-pgsql, never both)
   - a fixture `config/database.php` driven by env
   - a MySQL create/drop-database helper using `MARKO_TEST_MYSQL_*`, with the database name suffixed by `TEST_TOKEN`
   - the same skip-or-fail-on-`MARKO_INTEGRATION_REQUIRED` rule as the queue round-trip tests
   - the MySQL cases in their own file, so the CI MariaDB steps can select them by path

## Important (Should fix before building)

3. **Task 001: deleting the migration classes leaves live references.** These still reference the classes:
   - `tests/Integration/App/Fixture/database/migrations/2026_01_01_000003_create_jobs_table.php` and `..._000004_create_failed_jobs_table.php` (`return new CreateJobsTable();`)
   - `packages/queue-database/tests/Fixtures/SqliteConnection.php`
   - `MySqlRoundTripTest` and `PgSqlRoundTripTest`

   With only 001 merged, the main integration suite fatals. Fix: Task 001 replaces the two fixture migrations with inline documented DDL (moved from 005) and SqliteConnection with inline DDL. Task 003 still owns the round-trip tests. Task 005 now only touches the sessions migration (000005).

4. **Task 005: `TruncateDatabase` needs a `TestDatabase` and a testing environment.** Its constructor takes `TestDatabase`. `truncate()` calls `assertDisposable()`, which refuses outside `testing`/`test`. `TestDatabase::boot()` also caches one app per path per process. Fix: wrap the already-booted app with `new TestDatabase($app)` and call `truncate()` inside `withIntegrationAppEnv('testing', ...)`. Assert that `tables()` contains `failed_jobs`, `jobs` and `sessions`.

5. **Task 003, 004: the legacy-DDL "empty diff" check needs the same settle step as `MigrateCommand::calculateDiff()`.** MariaDB reports `current_timestamp()` and Postgres reports `CURRENT_TIMESTAMP` in its own spelling. A raw introspector-vs-entity compare drifts on `created_at` and `failed_at`. Fix: follow `packages/database-{mysql,pgsql}/tests/Integration/SchemaDiffSettlesTest.php`, i.e. settle with `ExpressionDefaultCanonicalizer`, then `DiffCalculator`. Restrict the diff to the plan's tables so unrelated tables in the shared `marko_test` database don't leak in.

6. **Task 006: CI paths are not named.** The MariaDB steps select suites by path. Fix: list the exact paths: `packages/queue-database/tests/Integration/MySqlRoundTripTest.php`, `packages/session-database/tests/Integration/MySql`, and Task 005's MySQL file.

7. **Task 007: stale copy beyond the two named sections.** `queue-database.md` line 6 says "Includes migrations for both tables" and line 32 says "the bundled migration creates". Upgrading apps whose own migration does `return new CreateJobsTable();` (the pattern the fixture used) will fatal once the classes are gone. Fix: update both lines and add a short upgrade note: replace such a migration with inline DDL, or delete it if the tables already exist.

## Minor (Nice to address)

- Task 002's `it lives in src/Entity so db:migrate discovers it` largely duplicates the mapping test. Consider asserting `EntityDiscovery::discoverInPath()` returns the class, which proves discovery rather than file location.
- `DatabaseSessionHandler`'s `ON DUPLICATE KEY UPDATE ... VALUES(payload)` is deprecated on MySQL 8.x. It is out of scope (#338), but Task 004 may surface warnings on MySQL 8.4.
- Task 007's "test descriptions" are prose checks. There is no docs-content test harness, so the worker should not invent one.

## Questions for the Team

- Delete `CreateJobsTable`/`CreateFailedJobsTable` outright, or keep them as `@deprecated` shims for one release? Deleting them is a BC break for apps that wired them into their own migration files. The plan currently deletes them and adds an upgrade note.
- Apps that created `sessions` with a different shape (for example the fixture's `id VARCHAR(255)`) will get an `ALTER` generated on their next dev `db:migrate`. Should the docs mention that?
