# Task 002: Serve never-expiring entries in FilePageCacheDriver

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`lookup()` rejects payloads whose `expires_at` is `null` because of `isset()`, so entries stored with an effective TTL of `0` are written on every request and never served. Accept `null` as "never expires" while still rejecting a missing or malformed `expires_at`.

## Context
- Related files: `packages/page-cache-file/src/Driver/FilePageCacheDriver.php`, `packages/page-cache-file/tests/helpers.php`, `packages/page-cache-file/tests/Unit/Driver/FilePageCacheDriverTest.php`
- Patterns to follow: `createPageCacheFileDriver($tmpDir, $defaultTtl, $clock)` with `FakeClock`; never `time()`/`sleep()`

## Requirements (Test Descriptions)
- [x] `it serves an entry stored with a zero effective ttl`
- [x] `it keeps serving a never-expiring entry however far the clock advances`
- [x] `it removes a never-expiring entry with purgeUrl, purgeTag and clear`
- [x] `it misses and deletes a positive-ttl entry once the clock passes expires_at`
- [x] `it treats a payload without expires_at as a miss`
- [x] `it treats a payload with a non-integer expires_at as a miss`

## Acceptance Criteria
- All requirements have passing tests
- Expiry comparison still uses the injected clock
- Use `array_key_exists('expires_at', $data)` plus `is_int(...) || === null` (not `isset`) for the `expires_at` check
- Update `store()` docblock to `@throws ConfigNotFoundException|PageCacheException|RandomException` (task 001 makes `PageCacheConfig::defaultTtl()` throw `PageCacheException` on a negative value; the class already exists, so no dependency on 001 is needed). This task owns all edits to `FilePageCacheDriver.php`.

## Implementation Notes
(Left blank - filled in by programmer during implementation)
