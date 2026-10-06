# Task 006: Docs for adopting packages

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document that cache-array, cache-file, cache-redis, ratelimiter and page-cache-file read time through `ClockInterface`, with a FakeClock example where time is user-visible (cache, ratelimiter), and list them in clock.md.

## Context
- Related files: packages/docs-markdown/docs/packages/{cache,cache-array,cache-file,cache-redis,ratelimiter,page-cache-file,clock}.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs pages mention ClockInterface for each adopting package`
- [x] `cache and ratelimiter docs show freezing time with FakeClock`
- [x] `clock.md lists the adopting packages`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
cache.md gained 'Expiry and the Clock' (FakeClock example), cache-array.md 'Time and Expiry', ratelimiter.md 'Testing with a Frozen Clock'; cache-file/cache-redis/page-cache-file storage details mention ClockInterface; clock.md lists the adopting packages. The examples were executed as a throwaway Pest test.
