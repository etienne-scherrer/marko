# Devil's Advocate Review: test-suite-fail-on-notices

## Critical (Must fix before building)
None. Task file sets for 003-006 do not overlap, every `createMock()` without `expects()` is in a package one of them covers (the remaining `packages/log` and `database/tests/Repository` mocks already have `expects()`), and paratest computes its exit code through PHPUnit's `ShellExitCodeCalculator`, so `failOn*` takes effect under `--parallel`.

## Important (Should fix before building)
1. **Task 002: wrong test path.** The existing write-failure test is in `packages/core/tests/Unit/Discovery/DiscoveryCacheTest.php` (line ~380), not `packages/core/tests/Discovery/`. A worker could create a duplicate file.
2. **Task 002: `error_get_last()` can be stale or null.** Without `error_clear_last()` before each `@` call, the exception can carry an earlier, unrelated error. If `error_get_last()` is null, `reason` must fall back to "no reason". Also, `@` does not skip a custom error handler: it still runs, with `error_reporting()` masked. So the "emits no PHP warning" test has to check `error_reporting() & $errno`, or it counts the suppressed warning.
3. **Task 002: tests that rely on read-only directories.** `chmod 0555` does not block root (docker, some CI images), so the test passes for the wrong reason or fails. Skip it when `posix_geteuid() === 0` or when the directory is still writable. The existing command test restores permissions only after its assertions. Restore them in `finally` so a failing assertion does not leave a temp dir that cannot be deleted.
4. **Plan, tasks 003-006: doubles created once and shared by many tests.** When a `beforeEach`/`setUp` double gets `expects()` in only some tests, switching it to a stub breaks those tests, and keeping it as a mock leaves notices in the others. Create the mock locally in the tests that set expectations and use a stub in the shared setup.
5. **Task 007: the guard test has no location or method.** Specify `tests/PhpunitConfigTest.php` (Monorepo suite), which parses `phpunit.xml` with SimpleXML and asserts the six attributes are `"true"` and `failOnSkipped` is absent or false.
6. **Task 008: suites that only run nightly are left out.** The Nightly workflow runs `composer test:all`: the roadrunner E2E (`integration-destructive`, with an `rr` binary), mail-smtp stream-socket integration, and Redis suites. It also runs `IntegrationVerificationTest`, which runs `composer update` and then the suite in a fresh vendor. All of these now run under the new flags. Run the roadrunner/mail-smtp/Redis files locally. Explicitly do NOT run `IntegrationVerificationTest` in the worktree (it deletes `vendor/`). Treat it as a known gap verified by the first nightly.

## Minor (Nice to address)
- Task 001: if `known-drivers.php` is empty, there are zero assertions and the test is still risky. Not a real case today.
- Task 001: the "counts an assertion" tests can measure `Assert::getCount()` before and after the call.
- Task 003-006 headers list notice counts (44/~62/~34/~30) that are estimates. The final check belongs to 007.

## Questions for the Team
- `failOnDeprecation` also counts deprecations raised inside vendor code (indirect) because `<source>` sets no `ignoreIndirectDeprecations`/`restrictDeprecations`. The nightly `IntegrationVerificationTest` runs `composer update`, so a new Latte/Twig/PSR release that deprecates something on PHP 8.5 will turn nightly red. Is that the loud behaviour you want, or should `<source ignoreIndirectDeprecations="true">` be set?
