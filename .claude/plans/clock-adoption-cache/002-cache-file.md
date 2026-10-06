# Task 002: Adopt clock in cache-file

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `FileCacheDriver` and use it for expiry, created_at, increment and the reported item expiry.

## Context
- Related files: packages/cache-file/src/Driver/FileCacheDriver.php, packages/cache-file/tests/Unit/FileCacheDriverTest.php, packages/cache-file/composer.json
- Raw fixtures such as `writeExpiredCacheEntry()` must derive `expires_at`/`created_at` from the test clock (`$clock->now()->getTimestamp()`), not the real `time()`. The tests freeze FakeClock at a fixed instant, so a fixture based on the real time would fall on a different timeline and the "expired" entry would look fresh.
- `increment()` must read the clock once and use that value for both the expiry check and the new `expires_at`/`created_at`.

## Requirements (Test Descriptions)
- [x] `it keeps an entry until its ttl has elapsed on the clock`
- [x] `it expires an entry one second after its ttl on the clock`
- [x] `it records created_at from the clock`
- [x] `it reports the item expiry relative to the clock`
- [x] `it restarts an expired counter relative to the clock on increment`
- [x] `requires marko/clock` (package structure)

## Acceptance Criteria
- All requirements have passing tests
- No time()/new DateTimeImmutable() left in src

## Implementation Notes
`writeExpiredCacheEntry()` takes `$now` from the test clock. The old 'uses default ttl when not specified' test only checked a pre-expired fixture; rewrote it to cross the default TTL with the clock.
