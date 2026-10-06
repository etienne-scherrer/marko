# Task 002: DiscoveryCache Write Failure Carries the OS Reason

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`DiscoveryCache::write()` calls `file_put_contents()` and `rename()` unguarded, so a failed write emits a raw PHP warning before `DiscoveryCacheException::notWritable()` is thrown, and the exception drops the real reason. Suppress the warning and fold `error_get_last()['message']` into the exception through a new optional `reason` argument.

## Context
- Related files: `packages/core/src/Discovery/DiscoveryCache.php`, `packages/core/src/Exceptions/DiscoveryCacheException.php`, `packages/core/tests/Command/DiscoveryCacheCommandTest.php` (line ~146), `packages/core/tests/Unit/Discovery/DiscoveryCacheTest.php` (existing notWritable test at line ~380; add new cache tests here, do not create a new `tests/Discovery/` file), and the existing exception tests under `packages/core/tests/`
- The `mkdir()` path already uses `@`; give it the reason too.
- Call `error_clear_last()` immediately before each `@mkdir`/`@file_put_contents`/`@rename` so the reason cannot be a stale, unrelated error; when `error_get_last()` is null, pass no reason (context unchanged).
- On rename failure, keep `@unlink($tmp)` but capture the rename reason *before* unlinking.
- `@` does not skip a custom error handler (it still runs with `error_reporting()` masked). The "no PHP warning" test must capture via `set_error_handler` and only count errors where `error_reporting() & $errno`, and must `restore_error_handler()` in `finally`.
- Read-only directory tests: `chmod 0555` does not block root. Skip with a clear message when `function_exists('posix_geteuid') && posix_geteuid() === 0` or when `is_writable($dir)` is still true. Restore permissions and clean up in `finally` (also fix the existing `DiscoveryCacheCommandTest` case, which only restores permissions after its assertions).

## Requirements (Test Descriptions)
- [x] `it includes the reason in the notWritable context when one is given`
- [x] `it keeps the notWritable context unchanged when no reason is given`
- [x] `it throws notWritable with the operating system reason when the cache directory is read-only`
- [x] `it emits no PHP warning when the cache file cannot be written`
- [x] `it does not report a stale earlier error as the reason`
- [x] `it returns a non-zero exit code and a helpful message (catching DiscoveryCacheException::notWritable) when the cache cannot be written` (asserts the reason is in the output)

## Acceptance Criteria
- The `DiscoveryCacheCommandTest` warning is gone
- No `@` without the reason folded into the exception

## Implementation Notes
Replaced `@` with a scoped `set_error_handler` (`DiscoveryCache::capturing()`) that captures the warning message, because PHPUnit reports `@`-suppressed warnings from source code. Reason is passed to `notWritable($path, $reason)` and appended to context. `DiscoveryCacheCommand` now also prints `getContext()` so the reason reaches the output. Tests added to DiscoveryCacheTest (exception tests live there); read-only tests skip as root.
