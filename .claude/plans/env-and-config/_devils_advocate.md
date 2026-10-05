# Devil's Advocate Review: env-and-config

## Critical (Must fix before building)

1. **Task 005 contradicts an existing test.** `packages/errors-simple/tests/Unit/EnvironmentTest.php:35` (`defaults to development when no environment variable set`, `new Environment(envVars: [])`) asserts development. Task 005 says "existing errors-simple tests still pass", which can't be true once unset means production. Fix: say outright that this test must be replaced by the new `treats an unset environment as production` test.

2. **The `AppEnvironment` contract for injected `$variables` is undefined (tasks 002, 005).** Today errors-simple's `getEnvVar()` falls back to `getenv()` even when `$envVars` is injected but lacks the key. If `AppEnvironment` does the same, injected-array tests become host-dependent: a dev machine or CI with `MARKO_ENV`/`APP_ENV` exported flips results. Fix: when `$variables !== null`, treat it as the only source (no `$_ENV`/`getenv` fallback). When null, read lazily on each call, not snapshotted in the constructor. Lookup order: `MARKO_ENV` (`$_ENV`, then `getenv`), then `APP_ENV` (`$_ENV`, then `getenv`). Treat an empty string as unset. `name()` returns the trimmed, lowercased value, or `production`.

## Important (Should fix before building)

3. **Task 004: how to mark the singleton.** `BindingRegistry::registerModule()` supports a list-style `singletons` entry (`[ConfigRepositoryInterface::class]` → `container->singleton()`). Moving the closure into a keyed `singletons` map would break the existing `ModuleBindingsTest` (it asserts the closure is under `bindings`). Fix: keep the closure in `bindings` and add list-style `'singletons' => [ConfigRepositoryInterface::class]`.

4. **Task 003: how `DiscoveryEnvironment` gets `AppEnvironment` is unspecified.** `DiscoveryEnvironment` is created with `new` in `Application` (line 167) and in tests, and is autowired through `DiscoveryCache` by `DiscoveryCacheCommand`/`DiscoveryClearCommand`. Fix: add an optional constructor argument `?AppEnvironment $appEnvironment = null` (defaulting to `new AppEnvironment()`). `Application` creates one `AppEnvironment`, registers it with `container->instance()` next to `ProjectPaths` (before module bindings), and passes it to `new DiscoveryEnvironment($appEnvironment)`.

5. **Task 003: the test env snapshot misses `MARKO_ENV` and `getenv`.** `cacheTestSnapshotEnv()` in `ApplicationDiscoveryCacheTest` only saves `APP_ENV`, `DISCOVERY_CACHE_ENABLED` and `DISCOVERY_CACHE_PATH`. Once the gate honours `MARKO_ENV` and falls back to `getenv`, a leaked or host `MARKO_ENV` changes the outcome. Fix: add `MARKO_ENV` to the snapshot, and clear and restore the `putenv` state for these keys.

6. **Task 001: global `$_ENV` pollution.** `Application::initialize()` runs `EnvLoader::load()` whenever marko/env is autoloadable, which is always in the monorepo. After this change every Application test copies the whole host `getenv()` into `$_ENV` for the rest of the process. Fix: task 001 tests must snapshot and restore the entire `$_ENV` array and any `putenv` keys in `afterEach`. Mirroring must use `getenv()` with no arguments and skip keys already present.

7. **Task 005: semantics of `isDevelopment()` for `staging`.** `AppEnvironment::isDevelopment()` is false for `staging`, but errors-simple's `isDevelopment()` is `!isProduction()`. Delegating both would create a new "neither" state in errors-simple. Fix: only `isProduction()` delegates (`new AppEnvironment($this->envVars)`); `isDevelopment()` stays `!isProduction()`.

8. **Task 006: a stale doc statement is missing from the task.** `core.md:275` says the boot gate "reads `DiscoveryEnvironment` directly". It now delegates to `AppEnvironment`, uses a `getenv` fallback and gates on `!isDevelopment()` (so `local` and `dev` skip the cache). Add this to task 006, plus an errors-simple.md requirement (the docs task currently has no errors-simple requirement).

## Minor (Nice to address)

- Task 001 test 3 (`keeps a real environment variable over the same key in .env`) already passes with the current `processLine()` check. Fine as a regression guard, but it won't drive any code.
- `errors-advanced` `PrettyHtmlFormatter::isProduction()` keeps its own `=== 'production'` check (so `prod` is not production there). Out of scope; candidate follow-up.

## Questions for the Team

- Should `DiscoveryEnvironment::environment()` keep returning the raw (lowercased) name, or should it be removed now that `Application` uses `AppEnvironment::isDevelopment()` directly?
