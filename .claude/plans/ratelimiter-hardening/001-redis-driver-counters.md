# Task 001: Redis driver: integer-aware reads + atomic increment

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Counters written by `increment()` are bare integers without an HMAC envelope, so every read path crashes on them. Add a private `decode()` that returns `int` for integer payloads and verifies+unserializes everything else; make `increment()` atomic via a Lua script.

## Context
- Related files: packages/cache-redis/src/Driver/RedisCacheDriver.php, packages/cache-redis/tests/Unit/RedisCacheDriverTest.php (MockRedisClient needs `eval`)
- Do NOT touch RedisConnection.php / module.php / config (owned by #166)

## Requirements (Test Descriptions)
- [x] `it returns an int from get() for a key written by increment()`
- [x] `it returns an int cache item from getItem() for a key written by increment()`
- [x] `it returns ints from getMultiple() for keys written by increment()`
- [x] `it still throws TamperedCacheValueException for a tampered non-integer value`
- [x] `it increments and sets the ttl in a single atomic script call`
- [x] `it restores a missing ttl on a counter that has none`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
