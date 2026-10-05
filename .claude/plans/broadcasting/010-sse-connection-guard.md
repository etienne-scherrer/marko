# Task 010: SSE max-connections guard

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add config/sse.php (max_connections null, retry_after), SseConnectionLimiter on CacheInterface slot keys (increment to acquire, delete to release, never get()), and the StreamingResponse guard.

## Requirements (Test Descriptions)
- [x] `it is not limited when max_connections is null`
- [x] `it acquires a free slot via increment`
- [x] `it returns null when every slot is taken`
- [x] `it releases a slot by deleting its key`
- [x] `it refuses the process-local array cache driver`
- [x] `it responds 503 with Retry-After when no slot is free`
- [x] `it releases its slot after streaming, on exceptions and on client abort`
- [x] `it streams without touching the cache when no limiter is given`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
