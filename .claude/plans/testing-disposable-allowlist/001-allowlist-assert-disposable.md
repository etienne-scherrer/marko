# Task 001: Allowlist assertDisposable() and update the exception

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Change `TestDatabase::assertDisposable()` from a production/development denylist to an allowlist that accepts only `AppEnvironment::isTesting()`. Update `DatabaseTestException::destructiveInEnvironment()` and the docblocks on `TestDatabase` and `TruncateDatabase` to describe the allowlist.

## Context
- Related files: `packages/testing/src/Database/TestDatabase.php`, `packages/testing/src/Database/TruncateDatabase.php`, `packages/testing/src/Exceptions/DatabaseTestException.php`, `packages/testing/tests/Feature/Database/TestDatabaseTest.php`, `packages/testing/tests/Feature/Database/TruncateDatabaseTest.php`, `packages/testing/tests/Unit/Exceptions/`, `packages/testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection.php`
- Patterns to follow: `withAppEnv()` helper, `RecordingConnection::$statements` for no-query assertions; `DestructiveCommandGuard` in marko/database for wording

### Gotchas (from devil's advocate review)
- **Process cache shadows the check.** `TestDatabase::boot()` caches per process in `private static array $booted` (keyed by realpath). Earlier tests in `TestDatabaseTest` (and other files in the same parallel worker) boot `databaseAppPath()` without `fresh`, so `boot(databaseAppPath(), fresh: true)` normally throws `conflictingFresh()` (whose message contains `fresh: true`) before `assertDisposable()` runs. The staging `fresh: true` test must clear `TestDatabase::$booted` via `ReflectionProperty` before the call and again in a `finally`, and must assert the staging-specific message (`"Refusing to rebuild the database (fresh: true) in the 'staging' environment"`), not just the exception class.
- **No-SQL is not observable through boot().** `boot()` builds the `Application` itself and throws before returning, so the test cannot reach the `RecordingConnection` instance. Add a static, resettable log to the fixture (e.g. `public static array $allStatements = []` on `RecordingConnection`, appended wherever `$statements` is). Reset it at the start of the test and assert it is empty after the refused boot. Also assert that nothing was cached: a following `withAppEnv('testing', fn () => TestDatabase::boot(databaseAppPath()))` must not throw `conflictingFresh`. Reset the cache again afterwards so later tests are unaffected.
- **No second list of names.** The exception must build the allowed names from `AppEnvironment::TESTING_NAMES` (e.g. `implode(', ', AppEnvironment::TESTING_NAMES)`), not hardcode "testing, test".
- **MARKO_ENV wins over APP_ENV.** `AppEnvironment::name()` reads `MARKO_ENV` first. The exception suggestion must say so (e.g. "Set APP_ENV=testing for the test run (MARKO_ENV, when set, takes precedence) ...").
- **Replace, don't duplicate, the old tests.** Fold `TestDatabaseTest` "refuses destructive operations in development" (asserts `'local'`) and `TruncateDatabaseTest` "refuses to truncate in development" into the new dataset/staging tests instead of keeping both.
- **Update every docblock that states the old policy:** the `TestDatabase` class docblock (lines 44-45), the `boot()` docblock ("refused in development too", line 74), the `assertDisposable()` docblock (lines 217-218), and the `TruncateDatabase` class docblock ("Refused in production and development", lines 27-28).

## Requirements (Test Descriptions)
- [x] `it allows destructive operations in the testing environments` (dataset: testing, test)
- [x] `it refuses destructive operations outside the testing environments` (dataset with named keys: production, unset => null, local, development, staging, qa); replaces the existing development-only test
- [x] `it refuses to rebuild the database with fresh: true in staging before running any SQL` (clears the boot cache, asserts the staging message, asserts the static statement log is empty and nothing was cached)
- [x] `it refuses to truncate in staging before running any SQL` (replaces the existing development-only truncate test)
- [x] `it describes the testing-only allowlist in the destructive environment exception` (message/context list the names from `AppEnvironment::TESTING_NAMES`; suggestion mentions `APP_ENV=testing` and that `MARKO_ENV` takes precedence)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
- `assertDisposable()` now checks `!$environment->isTesting()`.
- `boot()` runs the `fresh` disposable check right after `assertNotProduction()`, before the driver check and before the Migrator is resolved, so a refused rebuild never touches the database.
- `RecordingConnection` records through a private `record()` that also appends to a static `$allStatements` log.
- Exception context builds the names from `AppEnvironment::TESTING_NAMES`; new unit test in `tests/Unit/Exceptions/DatabaseTestExceptionTest.php`.
