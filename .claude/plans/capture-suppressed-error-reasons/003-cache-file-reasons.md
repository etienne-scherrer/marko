# Task 003: cache-file directory and write failure reasons

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`FileCacheDriver::ensureDirectoryExists()` calls `@mkdir` on every write, raising and hiding `mkdir(): File exists`. `write()` returns `false` with no reason when the temp write or rename fails. Only call mkdir when needed, and throw a `FileCacheException` (extends `CacheException`) carrying the OS reason.

## Context
- Related files: packages/cache-file/src/Driver/FileCacheDriver.php, packages/cache-file/src/Exceptions/FileCacheException.php (new), packages/cache-file/tests/Unit/FileCacheDriverTest.php
- Decision: a failed write throws `FileCacheException::writeFailed()` rather than returning `false`, so the reason is never dropped (loud errors). Directory failure moves from bare `RuntimeException` to `FileCacheException::directoryNotCreatable()`.

- **Existing test conflict:** `FileCacheDriverTest.php:401` (`leaves no orphan tmp file when the rename step fails`) asserts `set()` returns `false`. REWRITE it into the rename-reason test below (do not add a duplicate). Keep its technique: make `{cachePath}/{hash('xxh128', $key)}.cache` a directory so `rename()` fails.
- Directory creation must be race-tolerant: call `mkdir()` only when `!is_dir()`, and if `mkdir()` fails, re-check `is_dir()` before throwing (a concurrent creator may have won), exactly as `DiscoveryCache::write()` does.
- Failure tests must not rely on `chmod` (CI may run as root). Block paths with files or directories instead: a regular file at the config path's parent makes `mkdir` fail ("Not a directory"); a directory at the target makes `rename` fail.
- BC: `FileCacheException` extends `CacheException` (extends `\Exception`, NOT `RuntimeException`). Update `@throws` on `set()`, `setMultiple()` and `increment()` from `RuntimeException` to `FileCacheException`, and remove the now-unused `use RuntimeException`. Note in Implementation Notes that `set()` now throws instead of returning `false`, so task 006 can document it.
- "No warning recorded" assertions: install a test-level `set_error_handler` that records EVERY call (do not filter by `error_reporting()`), so a leftover `@mkdir` would still be detected; restore it in `finally`.

## Requirements (Test Descriptions)
- [x] `it does not call mkdir when the cache directory already exists` (no warning recorded across repeated writes)
- [x] `it throws FileCacheException with the OS reason when the cache directory cannot be created`
- [x] `it throws FileCacheException with the rename reason and leaves no orphan tmp file when the rename step fails` (replaces the existing line-401 test)
## Acceptance Criteria
- All requirements have passing tests
- Existing tests at lines 441, 451, 461 (directory exists / created on first write) keep passing
- Only cleanup `@unlink($tempPath)` remains

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
