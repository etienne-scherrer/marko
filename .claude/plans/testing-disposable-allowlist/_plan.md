# Plan: Testing Disposable Allowlist

## Created
2026-10-05

## Status
completed

## Objective
Make the destructive `marko/testing` database helpers (`TestDatabase::boot(fresh: true)` and `TruncateDatabase::truncate()`) run only when `APP_ENV` is a testing environment, matching the allowlist policy the destructive `db:*` commands adopted in #259.

## Related Issues
Closes #265

## Discovery Notes
- `TestDatabase::assertDisposable()` (`packages/testing/src/Database/TestDatabase.php`) refuses only production and development, so `staging`, `qa` or a typo passes and the suite truncates or rebuilds whatever `config/database.php` points at.
- `AppEnvironment::isTesting()` (`packages/core/src/Environment/AppEnvironment.php`) already defines the testing names (`testing`, `test`); the ticket forbids adding another list.
- `DatabaseTestException::destructiveInEnvironment()` and the docblocks on `TestDatabase` and `TruncateDatabase` describe the old denylist.
- `packages/docs-markdown/docs/packages/testing.md` "Test environment" describes the old policy; the database docs' [Environment Behaviour](/docs/packages/database/#environment-behaviour) section documents the CLI allowlist.
- Tests use `withAppEnv()` and the `RecordingConnection` from `tests/DatabaseApp` to assert no SQL ran.
- `assertNotProduction()` (non-destructive migrate on `boot()`) stays as is.

## Scope

### In Scope
- Allowlist in `assertDisposable()` via `AppEnvironment::isTesting()`
- Exception message/context describing the allowlist
- Docblock updates in `TestDatabase` and `TruncateDatabase`
- Tests for every environment named in the exit criteria and no-query assertions for `staging`
- Docs: "Test environment" section in testing.md, linking to the database destructive-command policy

### Out of Scope
- A `--force`-style escape hatch for the test helpers
- Changing `assertNotProduction()` / non-destructive `boot()`
- TestClient changes (#255)

## Success Criteria
- [x] `assertDisposable()` passes for `testing` and `test`, throws for production, unset, local, development, staging, qa
- [x] `boot(fresh: true)` and `truncate()` refuse `staging` before any SQL runs
- [x] Exception message and context describe the allowlist
- [x] testing.md "Test environment" states the allowlist and links to the database docs
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Allowlist assertDisposable() and update the exception | - | completed |
| 002 | Document the allowlist in the testing docs | 001 | completed |

## Architecture Notes
- Reuse `AppEnvironment::isTesting()`; no new list of environment names.
- Breaking for anyone running destructive helpers under a non-testing, non-development `APP_ENV`; pre-1.0, no shim. Call out in the PR.

- Exception wording derives allowed names from `AppEnvironment::TESTING_NAMES`; it also notes that `MARKO_ENV` takes precedence over `APP_ENV`.

## Risks & Mitigations
- Existing tests run destructive helpers under other envs: all current destructive tests use `testing`; verified by running the package suite.
- `TestDatabase::boot()`'s per-process cache makes a `fresh: true` refusal test hit `conflictingFresh()` instead of the allowlist. Task 001 clears the cache via reflection and asserts the staging-specific message.
- `boot()` hides its connection on failure. Task 001 adds a static statement log to the `RecordingConnection` fixture so the no-SQL assertion is observable.
- Docs conflict with #255 (TestClient section): edit only the "Test environment" section.
