# Devil's Advocate Review: integration-test-suite

## Critical (Must fix before building)

1. **Tasks 001/002: linking "the monorepo packages" into `vendor/marko/*` must be limited to the #187 module set.**
   Several driver packages bind the same interface: `cache-array`, `cache-file` and `cache-redis` all bind
   `CacheInterface`; `queue-sync`, `queue-rabbitmq` and `queue-database` all bind `QueueInterface`; `session-file` and
   `session-database` both bind `SessionHandlerInterface`. If every package is linked, boot fails with a binding conflict.
   Task 001 builds the fixture project before task 002 picks the modules, so the list has to be fixed in 001.
   *Fix:* task 001 owns one constant module list (the list from 002) and links only those packages.

2. **Task 001: temp-project cleanup must not follow symlinks.** A recursive delete of the temp project that descends into
   `vendor/marko/*` (symlinks into `packages/*`) would delete monorepo source. *Fix:* unlink the links themselves and
   never recurse into a link. Add a test that proves the linked package survives cleanup.

3. **Task 001: `Helpers.php` is never loaded, and its function names can collide with existing ones.** `phpunit.xml`
   bootstraps `vendor/autoload.php`, and `tests/Pest.php` loads only the Expectations file. Package helpers are loaded
   through `composer.json` `autoload-dev.files`, and nothing in `autoload-dev.psr-4` maps `tests/`. Those files are loaded
   globally into every process, so reusing a name is a fatal redeclare that takes down the whole suite.
   `Marko\Tests\monorepoRoot()` already exists in `tests/FixtureTrackingTest.php`. *Fix:* add the file to
   `autoload-dev.files`, give it its own namespace (`Marko\Tests\Integration\App`), and prefix the function names.

## Important (Should fix before building)

1. **Task 001: the database reset has to use `DROP DATABASE ... WITH (FORCE)`.** `database-pgsql` binds
   `ConnectionInterface` as a plain binding, not a singleton, so PDO connections from earlier tests in the same process
   can still be open. A plain `DROP DATABASE` then fails with "being accessed by other users". The reset must also
   connect to the `postgres` maintenance database to run it.
2. **Task 001: the helper tests must not depend on the real environment.** The CI Integration job sets `DB_HOST`,
   `REDIS_HOST` and `MARKO_INTEGRATION_REQUIRED=1`. The "explains when DB_HOST is not set" and "throws when required"
   tests would behave differently in that job. *Fix:* the probe takes an env array (defaulting to the real env), and
   these tests stay outside the `integration-services` group.
3. **Tasks 001/002: restoring the global error handlers.** `SimpleErrorHandler::unregister()` calls
   `restore_error_handler()` and then `set_error_handler($previous)`, which pushes PHPUnit's handler a second time. The
   handler stack grows, and PHPUnit reports "did not remove its own error handlers" as risky. *Fix:* after boot, call
   `restore_error_handler()` and `restore_exception_handler()` exactly once each. The shutdown function cannot be
   removed, but it is harmless.
4. **Task 001: the command runner's argv shape.** `Input::getArguments()` drops the first two entries
   (`array_slice($args, 2)`), so `--once` and `--no-generate` are silently lost unless the input is
   `['marko', $command, ...$args]`.
5. **Tasks 001 to 002 contract: the database name and the autoload shim.** The committed `config/database.php` reads
   `env('DB_DATABASE', 'marko_integration')`. The harness writes the per-process name into `$_ENV['DB_DATABASE']` before
   boot (`env()` checks `$_ENV` first). The generated `vendor/autoload.php` has to require the monorepo autoloader by
   absolute path, because the relative `dirname(__DIR__, N)` used by the roadrunner fixture breaks under
   `sys_get_temp_dir()`.
6. **Task 002: how the fixture module is discovered and collected.** `ModuleDiscovery::discoverInApp` needs
   `app/integration/composer.json` with `extra.marko.module: true` and a PSR-4 autoload. The `Monorepo` testsuite scans
   all of `tests/`, so no fixture file can end in `Test.php`. The namespace needs to be unique (the roadrunner fixture
   already uses `App\Demo`).
7. **Tasks 005/003: the Redis address in CI, and the CiWorkflowTest invariant.** cache-redis ignores config and always
   connects to `127.0.0.1:6379` (#166). The job runs on the runner host, so the services need `5432:5432` and
   `6379:6379` port mappings and `DB_HOST`/`REDIS_HOST=127.0.0.1`. Service hostnames do not work. CiWorkflowTest
   requires `php-version: '8.5'` to appear exactly once per `runs-on: ubuntu-latest`.
8. **Task 001: there is no fixture yet.** Task 002 creates `Fixture/`, so the builder test in 001 needs a source-path
   parameter and a minimal temp source.

## Minor (Nice to address)

- The probe should skip before copying or linking anything, so `composer test` with no services stays fast.
- Every boot registers another `ModuleAutoloader` that points at a temp directory that will be deleted. This is harmless
  but accumulates. `Session::configure()` re-registers the save handler once per booted Session instance.
- Task 002 is the largest task: entities, repositories, a seeder, 3 jobs, an observer, a scheduled task, routes, auth
  and `#[Can]`, all under 3 tests.

## Questions for the Team

- Should fixture pieces that only todo rows use (failing jobs, async observer, scheduled task, `#[Can]` routes) be built
  now, or by the ticket that flips the todo? CLAUDE.md's "no pseudo-functionality" principle leans toward the latter.
- Should the Integration job be a required check? This is a repo setting, already marked out of scope.
