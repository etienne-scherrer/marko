# Task 001: Run the fixture Pest project once with an isolated cache and environment

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Rework `runFixturePestProject()` so the child Pest process runs once per test file, uses a unique per-run cache directory that is removed afterwards, inherits the parent environment minus paratest variables, and gets `-d memory_limit=2G`. Every assertion reports the child output on failure.

## Context
- Related files: `packages/testing/tests/Feature/PestPluginRegistrationTest.php`
- Patterns to follow: existing helper; `vendor/pestphp/pest/src/Plugins/Only.php` for the `only.lock` note

## Requirements (Test Descriptions)
- [x] `it registers toHavePushed in a Pest run whose Pest.php has no require` (assertions carry child output)
- [x] `it fails toHavePushed with an assertion failure when nothing was pushed` (assertions carry child output)
- [x] helper memoises the result so only one child process starts per run
- [x] child environment = parent env minus `PARATEST`, `TEST_TOKEN`, `UNIQUE_TEST_TOKEN`
- [x] cache directory is unique per run and deleted afterwards

## Gotchas (from devil's advocate review)
- Pest's `toContain(mixed ...$needles)` has NO message parameter: a second argument becomes another needle. Carry the child output via `expect(str_contains($output, '...'))->toBeTrue($output)` / `->toBeFalse($output)` and `->toBe(0, $output)` for the exit code.
- Global functions in Pest test files share one namespace across the whole `composer test` run. `removeDir`, `removeDirectory`, `cleanupDir`, `cleanupDirectory`, `removeDirRecursive`, `cleanupTempDir` already exist elsewhere. Use a unique name (e.g. `removeFixturePestCacheDirectory`) or a local closure for recursive delete.
- Build the child env from `getenv()` (no args), not `$_ENV` (empty when `variables_order` lacks `E`). Drop `PARATEST`, `TEST_TOKEN`, `UNIQUE_TEST_TOKEN`, and any key starting with `PEST_PARALLEL`.

## Acceptance Criteria
- Both tests pass, including under `--parallel`
- Code follows code standards

## Implementation Notes
Executed directly by the orchestrating agent in TDD order (task files are small and interdependent through one test file). See the PR for stress-test numbers.
