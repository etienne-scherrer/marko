# Task 002: AppEnvironment service in core

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Core\Environment\AppEnvironment` with `name()`, `isProduction()` and `isDevelopment()`. Reads `MARKO_ENV` then `APP_ENV`, from `$_ENV` with `getenv()` fallback; unset defaults to `production` so it fails safe.

## Context
- Related files: packages/core/src/Environment/AppEnvironment.php (new), packages/core/tests/Unit/Environment/AppEnvironmentTest.php (new)
- Optional `?array $variables` constructor argument lets tests (and errors-simple) inject values without touching globals.
- Contract (errors-simple and DiscoveryEnvironment build on this):
  - When `$variables !== null` it is the ONLY source: no `$_ENV`/`getenv()` fallback, so tests are host-independent.
  - When null, read lazily on every call (do not snapshot in the constructor).
  - Lookup order: `MARKO_ENV` (`$_ENV`, then `getenv`), then `APP_ENV` (`$_ENV`, then `getenv`). An empty string counts as unset.
  - `name()` returns the trimmed, lowercased value, or `'production'` when unset.
- Not `final`; `declare(strict_types=1)`.

## Requirements (Test Descriptions)
- [x] `it defaults to production when no environment variable is set`
- [x] `it treats production and prod as production case-insensitively`
- [x] `it treats development, dev and local as development case-insensitively`
- [x] `it prefers MARKO_ENV over APP_ENV`
- [x] `it reads APP_ENV from getenv when $_ENV lacks it`
- [x] `it is neither production nor development for other names such as staging`
- [x] `it uses only injected variables and ignores globals when variables are provided`
- [x] `it treats an empty value as unset`
- [x] `it reads the environment lazily on each call`
- [x] `it returns the lowercased name`
- Tests touching `$_ENV`/`putenv()` must restore `MARKO_ENV` and `APP_ENV` in `afterEach`.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
