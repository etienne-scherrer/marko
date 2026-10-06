# Task 004: Adopt clock in page-cache-file

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `FilePageCacheDriver` and use it for stored expiry, created_at and the lookup expiry check.

## Context
- Related files: packages/page-cache-file/src/Driver/FilePageCacheDriver.php, packages/page-cache-file/tests/helpers.php, packages/page-cache-file/tests/Unit/*, packages/page-cache-file/composer.json
- Test helpers: `createPageCacheFileDriver()` and `createPageCacheFileDriverWithPaths()` take an optional trailing `?ClockInterface $clock = null` that defaults to `new FakeClock()`, so existing callers keep working.
- Existing tests and fixtures that use the real `time()` will break once the driver reads a frozen FakeClock. Switch every one to the test clock's `now()->getTimestamp()`:
  - `helpers.php:74-75` `writeExpiredPageCacheEntry()` (`time() - 10`): take the clock or a `$now` int.
  - `FilePageCacheDriverTest.php:76` (`toBeGreaterThan(time())`), `:103-104`, `:119-120` (`time() + ttl ± 5` windows): replace with exact assertions against the clock.
  - `FilePageCacheDriverTest.php:256-257`, `:286-287` (`time() + 9999` raw payloads).
- Read the clock once in `store()` so `expires_at` and `created_at` agree.
- Out of scope: `lookup()` uses `isset($data['expires_at'])`, which rejects `null` expiry. Keep that behaviour unchanged (see _devils_advocate.md Questions).

## Requirements (Test Descriptions)
- [x] `it serves a stored page until its ttl has elapsed on the clock`
- [x] `it misses a stored page once the clock passes its expiry`
- [x] `it records expires_at and created_at from the clock`
- [x] `requires marko/clock` (package structure)

## Acceptance Criteria
- All requirements have passing tests
- No time() left in src

## Implementation Notes
Helpers take an optional clock defaulting to FakeClock; all real-time fixtures/asserts switched to the test clock with exact assertions. Pre-existing isset() null-expiry quirk left untouched (reported in PR).
