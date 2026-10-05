# Plan: Env and Config

## Created
2026-10-05

## Status
completed

## Objective
Make real environment variables reach `$_ENV`, share the config repository across resolves, and give the framework one definition of "which environment am I in" (`AppEnvironment`).

## Related Issues
Closes #160

## Discovery Notes
- `EnvLoader::load()` returns early when no `.env` exists and never copies real env vars into `$_ENV`, so with `variables_order=GPCS` every `$_ENV[...] ?? default` config read falls back to its default.
- `DiscoveryEnvironment` reads `$_ENV` only; `Application` skips the discovery cache only when `APP_ENV === 'development'`, while the skeleton ships `APP_ENV=local`.
- `packages/config/module.php` binds `ConfigRepositoryInterface` to a closure with no `singletons` entry, so each resolve re-runs `ConfigDiscovery::discover()`.
- `Marko\ErrorsSimple\Environment` has its own `MARKO_ENV`/`APP_ENV` logic via `getenv()`, with an injectable `$envVars` array used heavily in tests.
- Core must not depend on `marko/env` or `marko/config`.

## Scope

### In Scope
- Mirror `getenv()` into `$_ENV` at the start of `EnvLoader::load()` (no overwrite)
- `Marko\Core\Environment\AppEnvironment` (`name()`, `isProduction()`, `isDevelopment()`)
- `DiscoveryEnvironment` getenv fallback and delegation of `environment()` to `AppEnvironment`
- `Application` registers `AppEnvironment` as a container instance; cache gate uses `!isDevelopment()`
- `ConfigRepositoryInterface` shared as a singleton
- `errors-simple` `Environment` delegates production detection to `AppEnvironment`
- Docs: env.md, config.md, core.md, errors-simple.md

### Out of Scope
- Switching the 12 vendor config files from `$_ENV` to `env()`
- Wiring database command production guards (#170)

## Success Criteria
- [x] Every exit criterion in #160 has a passing test
- [x] All tests passing
- [x] Code follows project standards
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Mirror real env vars in EnvLoader | - | completed |
| 002 | AppEnvironment service in core | - | completed |
| 003 | DiscoveryEnvironment + Application wiring | 002 | completed |
| 004 | Share ConfigRepositoryInterface | - | completed |
| 005 | errors-simple Environment delegates to AppEnvironment | 002 | completed |
| 006 | Documentation updates | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- `AppEnvironment` takes an optional `?array $variables` constructor argument (like errors-simple's `$envVars`) so it is unit-testable without touching globals.
  - When non-null, the injected array is the ONLY source.
  - When null, it reads `$_ENV` then `getenv()` lazily on each call.
  - Lookup order: `MARKO_ENV` before `APP_ENV`. An empty string counts as unset.
  - `name()` returns the lowercased value (see task 002).
- `DiscoveryEnvironment` takes an optional `?AppEnvironment`. `Application` creates one instance, registers it, and passes it in (task 003).
- errors-simple: only `isProduction()` delegates; `isDevelopment()` stays `!isProduction()` (task 005).
- Config singleton uses list-style `'singletons' => [ConfigRepositoryInterface::class]`; the closure stays in `bindings` (task 004).
- Unset environment defaults to `production` (fail safe).

## Risks & Mitigations
- errors-simple behaviour change when env is unset (previously "development", now "production"): fail-safe default; documented in the PR and docs.
- Tests that mutate `$_ENV`/`putenv()` must restore global state in `afterEach`.
