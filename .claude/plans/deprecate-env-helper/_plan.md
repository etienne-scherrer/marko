# Plan: Deprecate the global env() helper

## Created
2026-10-06

## Status
completed

## Objective
Implement option B of #344: deprecate the global `env()` helper in `marko/env` (docblock `@deprecated` plus an `E_USER_DEPRECATED` naming the variable and its `Marko\Config\Env` replacement), plan its removal at 1.0, drop the stale `marko/env` requires from six packages, and rewrite the `marko/env` docs around the `.env` loader.

## Related Issues
Closes #344

## Discovery Notes
- `packages/env/src/functions.php` defines `env()` inside `if (!function_exists('env'))`, autoloaded via `autoload.files`. No shipped code calls it; `tests/ConfigEnvReadsTest.php` forbids it in shipped configs.
- `Marko\Config\Env` (marko/config) offers `string`, `nullableString`, `int`, `nullableInt`, `float`, `bool`, `list`. It treats unset and empty (`KEY=`) as "use the default", does not accept `(true)`/`null`/`empty` words, and throws `ConfigException` on unparseable values.
- `marko/env` must stay dependency-free, so the deprecation message names `Marko\Config\Env` as a string.
- debugbar, inertia, inertia-react/vue/svelte and vite require `marko/env` but contain no `env()` call or `Marko\Env` reference; all already require `marko/config`. Their docs pages list `marko/env` under Related Packages / dependencies.
- Both shipped error handlers (`errors-simple`, `errors-advanced`) report `E_USER_DEPRECATED` as non-fatal, so the deprecation surfaces in logs/output without breaking boot.
- `.claude/pr-review-process.md:138` still tells reviewers to require `marko/env` when a config uses `env()`.
- No GitHub milestones exist; removal at 1.0 is tracked by a follow-up issue.

## Scope

### In Scope
- `@deprecated` docblock naming `Marko\Config\Env` and the 1.0 removal
- `E_USER_DEPRECATED` on every call, naming the variable and an `Env::*` replacement chosen from the default's type
- Unit tests in `packages/env/tests`
- Keep the `function_exists` guard (removing it fatals any app that also loads another global `env()`, e.g. `illuminate/support`); document the shadowing risk in the Deprecated docs section
- Remove `marko/env` from the six packages' `require`; update their docs pages
- Rewrite `env.md` and `packages/env/README.md` around `EnvLoader`, with a Deprecated section mapping each coercion to `Env::*`
- Small `config.md` edit linking the Deprecated section; `.claude/pr-review-process.md` rule update

### Out of Scope
- Removing `env()` now (option C)
- Any change to `Marko\Config\Env`

## Success Criteria
- [x] Calling `env()` emits `E_USER_DEPRECATED` naming the variable and its `Env::*` replacement
- [x] `env()` return values unchanged
- [x] Six packages no longer require `marko/env`
- [x] Docs describe `marko/env` as the `.env` loader with a Deprecated `env()` section
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Deprecate env() with E_USER_DEPRECATED | - | completed |
| 002 | Drop stale marko/env requires and their docs mentions | - | completed |
| 003 | Rewrite env docs around the .env loader | 001 | completed |

## Architecture Notes
- The replacement suggested in the message is derived from the type of `$default`: `bool` → `Env::bool`, `int` → `Env::int`, `float` → `Env::float`, `string` → `Env::string`, `array` → `Env::list`, `null`/other → `Env::nullableString`.
- The mapping is an inline `match` in `env()`. Do not add a second global helper function.
- `trigger_error()` is the first statement in `env()`, so calls that fall back to the default also warn.
- Canonical message (tests in 001 and docs in 003 use it): `env('{KEY}') is deprecated and will be removed in Marko 1.0. Read it in a config file with Marko\Config\Env instead: Env::{method}('{KEY}', {default}). Env throws on a value it cannot parse and treats an empty value as unset.`
- `#[\Deprecated]` is not used: PHP would emit its own fixed message per call, which cannot include the variable name, and combining it with `trigger_error()` would double every notice.

## Risks & Mitigations
- Existing `EnvFunctionTest` assertions would report deprecations: tests capture `E_USER_DEPRECATED` with a scoped error handler.
- Apps without the skeleton that relied on a transitive `marko/env` for `.env` loading: documented in the PR body and the package docs (require `marko/env` directly).
