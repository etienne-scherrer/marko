# Devil's Advocate Review: testing-disposable-allowlist

## Critical (Must fix before building)

### C1. The `fresh: true` in staging test can pass for the wrong reason (Task 001)
`TestDatabase::boot()` caches per process in `private static array $booted`, keyed by `realpath($basePath)`. `TestDatabaseTest` already boots `databaseAppPath()` without `fresh` (lines 58-87), and Pest's parallel workers can run several test files in one process. So `TestDatabase::boot(databaseAppPath(), fresh: true)` under `staging` usually hits the cached branch and throws `conflictingFresh()` ("...fresh: true...") before `assertDisposable()` runs. A test that asserts only `DatabaseTestException` (or a message containing `fresh: true`) passes even if the allowlist is never implemented.

Fix: the test must clear `TestDatabase::$booted` via reflection before (and after, in `finally`) the call, and assert the staging-specific message (`"Refusing to rebuild the database (fresh: true) in the 'staging' environment"`), not just the exception class.

### C2. "Before any SQL" cannot be observed for `boot(fresh: true)` with the current fixture (Task 001)
`boot()` creates the `Application` internally and throws before returning, so the test cannot get the `RecordingConnection` to check `$statements`. (`Migrator`'s constructor runs no SQL; `reset()` → `ensureTable()` is the first SQL.) Without a seam, the worker will either drop the no-SQL assertion or invent something odd.

Fix: add a static, test-resettable log to the fixture (e.g. `public static array $allStatements` on `RecordingConnection`, appended wherever `$statements` is), reset it at the start of the test, and assert it is empty after the refused boot. Also assert that nothing was cached: a later `withAppEnv('testing', fn () => TestDatabase::boot(databaseAppPath()))` must not throw `conflictingFresh`.

## Important (Should fix before building)

### I1. The exception must not hardcode a second list of names (Task 001)
The ticket forbids another list. If `destructiveInEnvironment()` writes "testing or test" literally, it drifts from `AppEnvironment::TESTING_NAMES`. Build the names from `implode(', ', AppEnvironment::TESTING_NAMES)`.

### I2. MARKO_ENV takes precedence over APP_ENV (Tasks 001, 002)
`AppEnvironment::name()` reads `MARKO_ENV` first. A developer with `MARKO_ENV=local` exported in their shell and `APP_ENV=testing` in phpunit.xml gets refused, and the message says only "Set APP_ENV=testing". The suggestion text and the docs should mention that `MARKO_ENV`, when set, wins.

### I3. Existing development-only tests overlap the new dataset (Task 001)
`TestDatabaseTest` "refuses destructive operations in development" (line 89) and `TruncateDatabaseTest` "refuses to truncate in development" (line 107) assert the old denylist wording. They duplicate the new dataset. Replace them with the dataset rather than keeping both, and check that their asserted message still matches.

### I4. Not every docblock that describes the old policy is listed (Task 001)
The old wording appears in the `TestDatabase` class docblock (lines 44-45), in the `boot()` docblock ("this is refused in development too", line 74), in the `assertDisposable()` docblock (lines 217-218) and in the `TruncateDatabase` class docblock (lines 27-28). List them all explicitly.

### I5. The docs must explain why this is stricter than the CLI (Task 002)
The database docs' Environment Behaviour allows `db:rebuild` in development and accepts `--force` elsewhere. Readers following the link will see a different policy. The testing docs must say plainly that the test helpers allow only testing names, refuse development, and have no `--force` equivalent.

## Minor (Nice to address)
- testing.md's `fresh: true` section (around line 596) could link back to "Test environment".
- In Pest datasets, label the `unset` case with a named key (`'unset' => [null]`) so the test name is readable.

## Questions for the Team
- Should the PR description (or a changelog entry) explicitly call out the break for `staging`/`qa`/CI envs named `ci`? Some CI setups use `APP_ENV=ci`, which is now refused.
