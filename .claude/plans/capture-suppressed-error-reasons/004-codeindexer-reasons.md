# Task 004: codeindexer IndexCache reasons and warning-free rebuild

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Replace every `@` in `IndexCache` with `ErrorCapture`. Saving failures carry the OS reason in `IndexCacheException::cacheDirUnwritable($path, ?string $reason = null)`; a corrupt or unreadable cache still triggers a rebuild but raises no PHP warning; `invalidate()` throws when the cache file cannot be removed.

## Context
- Related files: packages/codeindexer/src/Cache/IndexCache.php, packages/codeindexer/src/Exceptions/IndexCacheException.php, packages/codeindexer/src/Contract/IndexCacheInterface.php, packages/codeindexer/tests/Unit/Cache/IndexCacheTest.php
- `cacheDirUnwritable(string $path, ?string $reason = null)`: append `": $reason"` to the context when non-null (same format as `DiscoveryCacheException::notWritable()`).
- `invalidate()` failure uses a NEW factory `IndexCacheException::cacheNotRemovable(string $path, ?string $reason = null)` (message about removing, not writing). Add `@throws IndexCacheException` to `invalidate()` on both `IndexCacheInterface` and `IndexCache`. (No callers of `invalidate()` exist in packages today.)
- ALL warning sources need `ErrorCapture`: `mkdir`, `file_put_contents` in `save()`; `file_get_contents` + `unserialize` in `loadTrackedPathsFromDisk()` (runs FIRST, via `isStale()`, for a corrupt file); and `file_get_contents` + `unserialize` in `load()` (the `(string) file_get_contents()` there has no `@` but can still warn).
- Test techniques (avoid `chmod` where possible; CI may run as root): write failure = make `.marko/index.cache` a directory. Invalidate failure cannot be blocked that way (`is_file()` would be false), so set `.marko` to 0555, skip when `function_exists('posix_geteuid') && posix_geteuid() === 0`, and restore 0755 in `finally` (pattern: `packages/core/tests/Unit/Discovery/DiscoveryCacheTest.php:436`).
- "No PHP warning" assertions must record every error-handler call (not filter by `error_reporting()`), so a leftover `@` is detected.

## Requirements (Test Descriptions)
- [x] `it includes the reason in cacheDirUnwritable context when one is given`
- [x] `it keeps the cacheDirUnwritable context unchanged without a reason`
- [x] `it throws IndexCacheException with the OS reason when the cache file cannot be written`
- [x] `it treats a corrupt cache file as stale and rebuilds without raising a PHP warning`
- [x] `it throws IndexCacheException::cacheNotRemovable with the OS reason when invalidate cannot remove the cache file`

## Acceptance Criteria
- All requirements have passing tests
- No `@` left in IndexCache

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
