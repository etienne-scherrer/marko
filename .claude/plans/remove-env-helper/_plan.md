# Plan: Remove the Deprecated env() Helper

## Created
2026-10-06

## Status
completed

## Objective
Remove the deprecated global `env()` function from `marko/env` in the 0.9.0 release, leaving `marko/env` as the `.env` loader (`EnvLoader`) only, and turn the docs' deprecation section into a "Removed in 0.9.0" upgrade note.

## Related Issues
Closes #355

## Discovery Notes
- `packages/env/src/functions.php` defines `env()` inside `if (!function_exists('env'))`, autoloaded through `autoload.files` in `packages/env/composer.json`. The root `composer.json` does not list the file itself.
- Nothing outside `packages/env` calls `env()`: no package source, config, stub, skeleton or test. The only other reference is the `tests/ConfigEnvReadsTest.php` guard and its fixture, which stay.
- Maintainer decision: remove now for 0.9.0, not at 1.0. Marko is pre-1.0; users can stay on 0.8.x until they migrate. The issue's "merge only after a tagged release carried the deprecation" gate is waived.
- Docs referencing the helper: `env.md` (deprecated section with before/after example and migration table from #360, `### env()` API entry), `config.md:200`, `packages/env/README.md:20`, `.claude/architecture.md:1001`, `.claude/code-standards.md:421`, `.claude/pr-review-process.md:138`.

## Scope

### In Scope
- Delete `packages/env/src/functions.php` and the `autoload.files` entry
- Delete `packages/env/tests/Unit/EnvFunctionTest.php`; add a regression test that `env()` is not defined
- Update `tests/ConfigEnvReadsTest.php` docblock ("removed" instead of "deprecated"); keep its `env()` check
- Docs: `env.md` "Removed in 0.9.0" upgrade note (keeps before/after example and migration table, gives the exact `Call to undefined function env()` error); `config.md`, env README, `.claude/architecture.md`, `.claude/code-standards.md`, `.claude/pr-review-process.md`

### Out of Scope
- Any change to `EnvLoader` or `Marko\Config\Env`
- A shim or replacement global helper

## Success Criteria
- [x] `function_exists('env')` is `false` with `marko/env` installed (regression test in `packages/env/tests`)
- [x] `EnvLoaderTest.php` unchanged and green
- [x] `tests/ConfigEnvReadsTest.php` still rejects `env()` in shipped configs
- [x] Docs updated; no doc still says `env()` is deprecated or "will be removed in 1.0"
- [x] All tests passing (`composer ci` green)
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Remove env() and add regression test | - | completed |
| 002 | Update docs and guidance for the removal | - | completed |

## Architecture Notes
- `marko/env` stays dependency-free.
- The regression test lives in `packages/env/tests/Unit` so it travels with the split package.

## Risks & Mitigations
- Apps still calling `env()` fatal at boot with `Call to undefined function env()`: documented upgrade note with the migration table; PR labelled `breaking` so the CHANGELOG calls it out.
- Stale Composer autoload still including the deleted file: run `composer update marko/env` after editing `composer.json`. `dump-autoload` is not enough, because path-repo metadata is cached in `vendor/composer/installed.json`. `composer.lock` is gitignored, so CI is unaffected.
- Apps that also install a library defining a global `env()` (e.g. `illuminate/support`) get no fatal error. Their leftover calls silently use that library's `env()`. The upgrade note says so and tells readers to search configs for `env(`.
