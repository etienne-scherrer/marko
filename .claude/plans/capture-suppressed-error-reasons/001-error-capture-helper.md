# Task 001: ErrorCapture helper in marko/core

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extract the scoped error-capturing pattern from `DiscoveryCache::capturing()` into a shared `Marko\Core\Support\ErrorCapture` so every package can fold a PHP warning's reason into its exception instead of using `@`.

## Context
- Related files: packages/core/src/Discovery/DiscoveryCache.php, packages/core/src/Support/ErrorCapture.php (new)
- Patterns to follow: `DiscoveryCache::capturing()` from PR #357
- State at review time: `packages/core/src/Support/ErrorCapture.php` and `packages/core/tests/Unit/Support/ErrorCaptureTest.php` already exist in this worktree (they include an extra `resets a reason left over from an earlier call` test). `DiscoveryCache::capturing()` is still present. Continue from that state: verify the tests, then remove `capturing()` and switch the three call sites to `ErrorCapture::run()` (the `$reason = null;` pre-assignments become redundant because `run()` resets the reason).

## Requirements (Test Descriptions)
- [x] `it returns the operation result and leaves the reason null when nothing is raised`
- [x] `it captures the warning message raised by the operation instead of emitting it`
- [x] `it joins several warnings raised by one operation in order`
- [x] `it restores the previous error handler after the operation returns`
- [x] `it restores the previous error handler when the operation throws`
- [x] `it captures a warning that the operation itself suppresses with @`
- [x] DiscoveryCache write tests keep passing using the shared helper

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
