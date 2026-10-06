# Task 005: Migrate broadcasting, debugbar, inertia and vite config files to Env

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Rewrite the broadcasting-amphp, broadcasting-mercure, broadcasting-pusher, debugbar, inertia, inertia-react/vue/svelte and vite config files. Replace `env()` and `filter_var`. Add `marko/config` to inertia-react/vue/svelte.

## Context
- Related files: packages/{pkg}/config/*.php, packages/inertia-{react,vue,svelte}/composer.json

## Requirements (Test Descriptions)
- [x] `it rejects MERCURE_COOKIE_SECURE=ture instead of dropping the Secure flag`
- [x] `it reads DEBUGBAR_ENABLED=off as false`
- [x] `it falls back to APP_DEBUG for debugbar.enabled`
- [x] `it derives vite.useDevServer from APP_ENV when VITE_USE_DEV_SERVER is unset`

## Acceptance Criteria
- Existing package tests still pass
- Tests snapshot/restore every `$_ENV`/`putenv` variable they touch

## Implementation Notes (from review)
- Use the task 001 contract exactly.
- Debugbar types must match the readers in packages/debugbar/src: all on/off keys -> `Env::bool`; `theme`, `storage.path` -> `Env::string`; `storage.max_files` -> `Env::int(..., 100, min: 0)` (read by `DebugbarStorage::configInt`); `options.database.slow_threshold_ms` -> `Env::float(..., 100.0, min: 0)` (read via `configFloat`); keep `enabled` falling back to `Env::bool('APP_DEBUG', false)`. Do not change debugbar's `configBool()` (out of scope).
- Vite: `useDevServer` => `Env::bool('VITE_USE_DEV_SERVER', Env::string('APP_ENV', 'local') === 'local')`. Keep the `'local'` fallback; do NOT switch to `AppEnvironment` (its default is `'production'`, which would flip the dev-server default).
- Mercure `cookie_secure` -> `Env::bool('MERCURE_COOKIE_SECURE', true)`; broadcasting-amphp origins/proxies -> `Env::list` with defaults `['*']` / `[]`.
- Do NOT remove `marko/env` from any composer.json, even when `env()` is no longer called. It loads `.env` into `$_ENV`.
- Add `"marko/config": "self.version"` to inertia-react/vue/svelte `require`.
