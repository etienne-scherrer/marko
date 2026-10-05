# Task 002: Fixture Application and Boot Case

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Create the `app/integration` fixture module (two entities and repositories, seeder, three jobs, async observer,
scheduled task, routes with rate limiting, `#[Can]`, session and auth), config files reading env vars, and committed
migrations. Boot it through `Application::boot()`.

## Context
- Related files: `tests/Integration/App/Fixture/**`
- Modules: database, database-pgsql, cache, cache-redis, queue, queue-database, scheduler, session, session-database,
  authentication, authorization, ratelimiter, routing, errors, errors-simple, config, core, encryption,
  encryption-openssl, cli
- Workaround: bind `WorkerInterface` in the fixture's module.php with a comment naming #161

## Requirements (Test Descriptions)
- [x] `it boots the fixture application with every installed module`
- [x] `it resolves the real driver bindings from module wiring`
- [x] `it discovers the fixture routes`

## Acceptance Criteria
- Boot goes through `Application::boot()`; no hand-constructed services
- The module set matches the constant list in task 001 (that list is the source of truth)

## Gotchas (from devil's advocate review)
- `app/integration/composer.json` must have `extra.marko.module: true` and a PSR-4 autoload under a unique namespace.
  The roadrunner fixture already uses `App\Demo`.
- No file under `Fixture/` may end in `Test.php`, because the `Monorepo` testsuite scans all of `tests/`.
- `config/database.php` reads `env('DB_DATABASE', 'marko_integration')`; the harness sets the per-process name.
  `DB_HOST`, `DB_PORT`, `DB_USERNAME` and `DB_PASSWORD` are also read from env.
- `config/encryption.php` must give a fixed test key (follow `packages/roadrunner/tests/Fixtures/app/config`).
- Boot cases use the harness boot, which restores the error and exception handlers that errors-simple pushes.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
