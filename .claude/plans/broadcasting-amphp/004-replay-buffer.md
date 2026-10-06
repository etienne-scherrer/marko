# Task 004: SseEvent and ReplayBuffer

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
SSE frame formatting and a bounded per-channel replay buffer with TTL eviction.

## Requirements (Test Descriptions)
- [x] `it formats id, event and data lines`
- [x] `it keeps at most replay_buffer events`
- [x] `it evicts events older than replay_ttl`
- [x] `it returns events after a sequence in order`
- [x] `it reports whether it can replay from a sequence`
- [x] `it resolves an event id to its sequence and returns null for unknown ids`
- [x] `it merges events from several channels in sequence order`
- [x] `it forgets a channel's history when the channel is dropped`
- [x] `it formats heartbeat, reset and reconnect frames`

## Implementation Notes
- Frame formats are fixed in `_plan.md` Shared contracts. `data:` carries only the JSON of `data`.
- If the same id appears on several channels, the id resolves to its latest sequence.
- Inject a clock (callable or `now` parameter) so TTL tests do not sleep.

## Acceptance Criteria
- All requirements have passing tests
