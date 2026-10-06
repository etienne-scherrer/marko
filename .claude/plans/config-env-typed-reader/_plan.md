# Plan: Typed Env Reader for Config Files

## Created
2026-10-06

## Status
completed

## Objective
Replace the unvalidated `(int)` casts, `filter_var` calls, hand-rolled bool lists and `env()` helper calls in every shipped `packages/*/config/*.php` with an explicit static `Marko\Config\Env` reader that throws `ConfigException` on invalid values, and tighten `ConfigRepository::getBool()`/`getInt()`.

## Related Issues
Closes #289. Relates to #293 (webhook config values left unvalidated), #275 (`PAGE_CACHE_TTL=0` means never expires), #269 (`token_expiration_days` rejects numeric strings).

## Discovery Notes
- 25 shipped config files read env; 30 `(int)` casts in 14 files, two `filter_var` bool reads (mercure `cookie_secure`, cors `supports_credentials`), the hand-rolled list in `core/config/discovery.php`, and `env()` in debugbar, inertia, inertia-react/vue/svelte and vite.
- Config files run inside `ConfigLoader` before the container exists, so the reader must be static and DI-free. `marko/config` already has `ConfigException` (message/context/suggestion).
- `core/config/discovery.php` lives in `marko/core`, which cannot depend on `marko/config` (config requires core). The boot gate already reads env through `Marko\Core\Discovery\DiscoveryEnvironment`; the config file is a mirror nobody reads. Decision: the config file delegates to `DiscoveryEnvironment`, and `DiscoveryEnvironment::enabled()` becomes strict (same token set as `Env::bool`, plus the documented "empty = disabled"), throwing `DiscoveryCacheException` on anything else.
- `marko/pubsub`, `marko/inertia-react`, `marko/inertia-vue`, `marko/inertia-svelte` don't declare `marko/config` directly; add it.
- `webhook.php` has no env reads; `WebhookConfig` validates only `timeout`. `max_retries`, `retry_delay`, `timestamp_tolerance` get range checks there (#293 follow-up).
- `TokenConfig` (#269) rejects numeric strings by design; docs told users to cast. Docs now show `Env::int(..., min: 1)`, which yields a real int.
- `ConfigRepository::getBool()` casts any scalar (`'off'` → true); `getInt()` accepts any `is_numeric`.

## Scope

### In Scope
- `Marko\Config\Env`: `string`, `nullableString`, `int` / `nullableInt` (`min`/`max`), `float`, `bool`, `list` (full contract in task 001)
- `ConfigRepository::getBool()` / `getInt()` tightening
- Migrating every shipped config file that reads env; structure test that greps for the old patterns
- `PAGE_CACHE_TTL=abc` regression test
- Webhook config range validation
- Strict `DiscoveryEnvironment::enabled()`
- Docs: config.md, env.md, every package page that reproduces a config file, architecture.md examples

### Out of Scope
- Deprecating or removing the global `env()` helper (follow-up)
- Debugbar's own `configBool()` reader
- Database package `src` (parallel PRs #323/#324)

## Success Criteria
- [x] `Env` unit-tested for valid values, defaults, empty strings and every rejection
- [x] Rejections name the variable, value and accepted forms
- [x] No `(int)`/`(float)` casts, `filter_var`, hand-rolled bool lists or `env()` on env values in shipped config files (structure test)
- [x] `PAGE_CACHE_TTL=abc` fails config load
- [x] `getBool('off')` is false, `getInt('1.5')` throws
- [x] Docs updated
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | `Marko\Config\Env` typed reader | - | completed |
| 002 | Tighten `ConfigRepository::getBool()` / `getInt()` (and `FakeConfigRepository`) | 001 | completed |
| 003 | Strict `DiscoveryEnvironment::enabled()`; discovery.php delegates | - | completed |
| 004 | Migrate infrastructure config files (cache, page-cache, redis, pubsub, queue, log, hashing, amphp, sse, cors, encryption, routing, translation) | 001 | completed |
| 005 | Migrate broadcasting, debugbar, inertia*, vite config files | 001 | completed |
| 006 | Validate webhook `max_retries`, `retry_delay`, `timestamp_tolerance` | - | completed |
| 007 | Structure test over all shipped config files (monorepo `tests/`) | 003, 004, 005 | completed |
| 008 | Docs | 001-007 | completed |

## Architecture Notes
- `Env` is a plain class with static methods (no `final`, no magic). It reads `$_ENV` first, then `getenv()`. Unset or `''` returns the default (the default is not validated against min/max; it is the author's literal).
- Bool tokens: `true/false/1/0/yes/no/on/off`, case-insensitive, trimmed; exposed as `Env::TRUE_VALUES` / `Env::FALSE_VALUES` and reused by `ConfigRepository`/`FakeConfigRepository` (core's `DiscoveryEnvironment` hardcodes the same set because core cannot import `marko/config`).
- Signatures, list semantics (trim, drop empties, reindex), int/float parsing and `$_ENV` scalar handling are fixed in task 001's Interface Contract. Tasks 002/004/005 build against it.
- `marko/env` stays required wherever it is today (it loads `.env`); only the `env()` calls go.
- The cross-package structure test lives in the monorepo `tests/` suite (packages are split into standalone repos).
- `discovery.php` `environment` now follows `AppEnvironment` (MARKO_ENV takes precedence, lowercased); the default is still `production`.
- Errors: `ConfigException` built by private static factories on `Env`, message naming the variable, context with the offending value, suggestion listing the accepted forms.

## Risks & Mitigations
- Behaviour change for `KEY=` on keys whose default is non-empty: documented ("empty = unset").
- Tests that set non-string `$_ENV` values: `Env` accepts scalar `$_ENV` entries by stringifying them.
- Tightened getters may break consumers relying on lax casting: run full suite and fix call sites.

## Implementation Decisions (resolving the review's deferred items)
- Empty value = default for every reader, including `Env::string`, as the issue specifies. Prefixes (`CACHE_PREFIX=` etc.) can no longer be emptied from the environment; override the key in the app's config file instead. Documented in config.md.
- `DISCOVERY_CACHE_ENABLED=` keeps meaning "disabled" (documented behaviour of the boot gate); only unrecognised values now throw.
- A non-scalar `$_ENV` entry throws `ConfigException` rather than falling back to `getenv()` (loud over silent).
- Strict int/bool parsing moved into `Marko\Config\ConfigValue`, shared by `ConfigRepository` and `FakeConfigRepository`.
- `env()`'s `null`/`empty` keywords are not special in `Env`; noted in env.md.
- Error messages are not truncated and don't name the config file; the variable name is enough to find the source.
