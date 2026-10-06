# Devil's Advocate Review: config-env-typed-reader

## Critical (Must fix before building)

1. **Task 007 test location breaks package splits.** The plan puts a test in `packages/config/tests` that scans every `packages/*/config/*.php`. Packages are split into standalone repos (`tests/SplitWorkflowTest.php`), and in a split `marko/config` repo that path does not exist. Cross-package scans belong in the monorepo `tests/` suite (precedent: `tests/Psr7ContainmentTest.php`). Fix: move it to `tests/ConfigEnvReadsTest.php`.
2. **Task 004 `sse.max_connections` cannot be expressed with the planned API.** It is `null` when unset or empty and an int otherwise. `Env` has `nullableString` but no nullable int, so a worker would fall back to a raw `$_ENV` read (which fails task 007) or invent an API on the spot. Fix: add `Env::nullableInt` to task 001, and pin down every method signature so tasks 004/005 build against a fixed contract.
3. **Task 003 contradicts an existing test.** `packages/core/tests/Unit/Discovery/DiscoveryEnvironmentTest.php:58-68` asserts that `'enabled'` reads as true. Strict mode makes that value throw. Fix: the task must rewrite that test explicitly.

## Important (Should fix before building)

4. **Task 001 leaves the `Env` contract undefined.** Not specified: list semantics (cors uses bare `explode`, broadcasting-amphp uses trim+filter+`array_values`, amphp uses `array_filter` and keeps keys); whether list defaults are arrays or strings; int parsing (`FILTER_VALIDATE_INT` with trim, overflow); which float forms are accepted (`1e3`, `inf`, `nan`); how non-string `$_ENV` values are handled (bool `false` stringifies to `''` and silently becomes the default). Fix: spelled out in task 001 and Architecture Notes.
5. **Task 002 leaves `FakeConfigRepository` lax.** `packages/testing/src/Fake/FakeConfigRepository.php` still does `(bool)` / `(int)` casts, so `getBool('off')` returns true under the fake and false in production. Tests written with the fake hide the difference. Fix: task 002 applies the same rules to the fake. It also shares the token set with `Env` (public constants), so task 002 now depends on task 001.
6. **Task 003 changes the `environment` value in `discovery.php`.** It used to be the raw `$_ENV['APP_ENV']`. Delegating to `DiscoveryEnvironment::environment()` brings `AppEnvironment` rules: `MARKO_ENV` takes precedence, and the value is trimmed and lowercased. The default stays `production`, so this is acceptable, but it must be deliberate and documented. Also: `enabled()` must trim to match `Env::bool`; it needs a named exception factory; and `core/src` must not import `Marko\Config` (an existing structure test checks this).
7. **Tasks 004/005 give no per-key types or bounds.** Debugbar reads `slow_threshold_ms` through `configFloat` and `max_files` through `configInt`. Several keys treat `0` as "disabled" (`max_connections_per_ip`, `log_interval`, `cors.max_age`), so `min: 1` would reject valid configs. Fix: add type and bound guidance, plus "check the consuming class before adding a bound".
8. **Tasks 004/005: `marko/env` must stay required.** After migration, debugbar/vite/inertia* no longer call `env()`, which invites removing `marko/env` from their composer.json. That package is what loads `.env` into `$_ENV`. Fix: tasks say keep it.
9. **Task 005 vite default.** `useDevServer` defaults from `APP_ENV` with fallback `'local'`, not `AppEnvironment`'s `'production'`. A worker trying to be consistent would flip the dev-server default. Fix: pin `Env::string('APP_ENV', 'local') === 'local'`.
10. **Env-state leakage in tests.** `Env` now reads `getenv()` too, and the suite runs `--parallel` with in-process `putenv`. Fix: every new test snapshots and restores `$_ENV` and `putenv` for the variables it touches.

## Minor (Nice to address)

- `ConfigException` context echoes the rejected value. That is harmless for ints and bools, but consider truncating long values.
- Exceptions thrown inside config files are not wrapped by `ConfigLoader`, so the message names the variable but not the file. This is acceptable.
- The task 007 regex for `env(` must not match `getenv(` or `Env::…(`. Banning `(string)` casts on env values would also help.
- Task 004 is large (15 files). It is mechanical, but it could be split if a worker runs short of context.
- Eager `Env::bool('APP_DEBUG', …)` inside the debugbar fallback throws on a bad `APP_DEBUG` even when `DEBUGBAR_ENABLED` is set. This is acceptable (loud).
- `env()` used to coerce `'null'` and `'(empty)'`; `Env` will not. This is undocumented.

## Questions for the Team

- "Empty = unset" means `CACHE_PREFIX=`, `PUBSUB_PREFIX=` and `BROADCASTING_AMPHP_CHANNEL_PREFIX=` can no longer set an empty prefix; they fall back to the non-empty default. Is that acceptable, or should `Env::string` return `''` as-is and apply only to typed readers?
- `DISCOVERY_CACHE_ENABLED=` means *disabled*, while every other `Env::bool` treats an empty value as unset (the default). Keep this inconsistency for backward compatibility?
