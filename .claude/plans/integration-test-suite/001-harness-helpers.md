# Task 001: Harness Helpers, Skip Reason, Compose File, Composer Script

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Create the helper functions every integration-services test uses: the skip-with-reason probe, required mode for CI,
fixture copying with generated `vendor/`, and the per-process Postgres database reset. Add the compose file and the
`composer test:integration` script.

## Context
- Related files: `tests/Integration/App/Helpers.php`, `tests/Integration/compose.yml`, `composer.json`
- Patterns to follow: `roadRunnerSkipReason()` in `packages/roadrunner/tests/Helpers.php`

## Requirements (Test Descriptions)
- [x] `it explains how to start the services when DB_HOST is not set`
- [x] `it explains how to start the services when REDIS_HOST is not set`
- [x] `it reports an unreachable host and port with the compose command`
- [x] `it throws instead of skipping when MARKO_INTEGRATION_REQUIRED is set`
- [x] `it suffixes the database name with the parallel test token`
- [x] `it builds a fixture project whose vendor modules link to the monorepo packages`
- [x] `it links only the integration module set into the fixture vendor directory`
- [x] `it removes the temp project without following vendor symlinks into the monorepo packages`
- [x] `it passes the script name and command ahead of the arguments to the command runner`

## Acceptance Criteria
- All requirements have passing tests that need no services
- `composer test:integration` runs `--group=integration-services`

## Contract and Gotchas (from devil's advocate review)
- **Loading and naming:** register `tests/Integration/App/Helpers.php` in `composer.json` `autoload-dev.files`.
  `tests/` has no psr-4 mapping. Use namespace `Marko\Tests\Integration\App` and prefix every function with
  `integration`. Files in `autoload-dev.files` share one process, so a redeclared name is a fatal error. For example,
  `Marko\Tests\monorepoRoot()` already exists.
- **Module set:** link only the #187 module set as one constant: core, config, cli, routing, errors,
  errors-simple, encryption, encryption-openssl, database, database-pgsql, cache, cache-redis, queue, queue-database,
  scheduler, session, session-database, authentication, authorization, ratelimiter. Linking every package fails boot with
  a binding conflict (cache-array, cache-file and cache-redis all bind `CacheInterface`, for example).
- **Cleanup:** call `unlink()` on the symlinks and never recurse into a link. A recursive delete that follows
  `vendor/marko/*` deletes `packages/*`.
- **Autoload shim:** the generated `vendor/autoload.php` requires the monorepo `vendor/autoload.php` by absolute path.
- **Builder input:** the project builder takes the fixture source directory as a parameter. Task 002 creates `Fixture/`,
  so this task's test uses a minimal temp source.
- **Environment:** the skip probe and required-mode helpers take an env array (defaulting to the real env), so their tests
  are deterministic in the CI Integration job, where `DB_HOST` and `MARKO_INTEGRATION_REQUIRED` are set. These tests are
  NOT in the `integration-services` group. Probe before building any temp project.
- **Database reset:** connect to the `postgres` maintenance database and run
  `DROP DATABASE IF EXISTS ... WITH (FORCE)`, then `CREATE DATABASE`. `FORCE` is needed because
  `ConnectionInterface => PgSqlConnection` is a non-singleton binding, so earlier tests can leave connections open.
  Before boot, write the per-process name to `$_ENV['DB_DATABASE']`; the committed config reads
  `env('DB_DATABASE', 'marko_integration')`.
- **Error handlers:** after `Application::boot()`, call `restore_error_handler()` and `restore_exception_handler()`
  exactly once each. Do not use `SimpleErrorHandler::unregister()`: it re-pushes the previous handler, which grows the
  stack, and PHPUnit marks the test risky.
- **Command runner:** build `new Input(['marko', $command, ...$args])`, because `Input::getArguments()` drops the first
  two entries. Capture output with `new Output($memoryStream)`.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
