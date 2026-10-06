# Task 001: Remove env() and Add Regression Test

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Delete the deprecated global `env()` function and its autoload entry so `marko/env` ships only `EnvLoader`. Replace its test file with a regression test proving the function no longer exists.

## Context
- Related files: `packages/env/src/functions.php`, `packages/env/composer.json`, `packages/env/tests/Unit/EnvFunctionTest.php`, `tests/ConfigEnvReadsTest.php`
- Patterns to follow: existing Pest tests in `packages/env/tests/Unit/EnvLoaderTest.php`
- After removing the `files` entry, run `composer update marko/env`, NOT `composer dump-autoload`. `marko/env` is a path repo, and `dump-autoload` rebuilds from the stale `vendor/composer/installed.json` snapshot, which still lists `src/functions.php`. If the file is deleted and only `dump-autoload` runs, every test fatals with "Failed opening required". Afterwards, confirm that `vendor/composer/autoload_files.php` no longer contains `marko/env/src/functions.php`.

## Requirements (Test Descriptions)
- [x] `it does not define a global env() function`
- [x] `it ships no autoloaded files, only the Marko\Env PSR-4 namespace`
- [x] `functions.php` and `EnvFunctionTest.php` are deleted
- [x] `tests/ConfigEnvReadsTest.php` docblock says the helper was removed; its `env()` check stays green
- [x] `vendor/composer/autoload_files.php` no longer references `marko/env/src/functions.php` (after `composer update marko/env`)

## Acceptance Criteria
- All requirements have passing tests
- `EnvLoaderTest.php` unchanged and green
- Code follows code standards

## Implementation Notes
Red first: EnvHelperRemovedTest failed on both tests while functions.php was autoloaded. Deleted functions.php and EnvFunctionTest.php, removed autoload.files, ran `composer update marko/env` (installed.json had cached the files entry). Green: packages/env and tests/ConfigEnvReadsTest.php pass. ConfigEnvReadsTest docblock now says env() was removed in 0.9.0 and the check names the file before the fatal.
