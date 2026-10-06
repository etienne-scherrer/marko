# Task 005: ChannelHub and SseConnection

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
One shared pubsub subscription per channel, in-process fan-out to connections, cancel on last leave.

## Requirements (Test Descriptions)
- [x] `it shares one subscription per channel across connections`
- [x] `it fans a message out to every connection on the channel`
- [x] `it cancels the subscription when the last connection leaves`
- [x] `it skips malformed messages and logs an error`
- [x] `it opens only one subscription when two connections join the same channel concurrently`
- [x] `it never blocks fan-out on a slow connection and closes it when its pending frames exceed the cap`
- [x] `it closes the channel's connections and logs an error when the subscription ends unexpectedly`
- [x] `it drops the channel's replay history when the subscription is cancelled`

## Implementation Notes
- `subscribe()` suspends (Redis connects asynchronously), so a second join during that suspension must await the same pending subscription (store a `Future`/`DeferredFuture` per channel), not open another.
- Fan-out must not suspend: use a non-blocking push per connection (`Queue::pushAsync` or an explicit bounded buffer) with a `MAX_PENDING_FRAMES` class constant; overflowing closes that connection only.
- Record each message in the ReplayBuffer before fanning out.
- Create `tests/Support/InMemoryPubSub` here (see `_plan.md` Shared contracts); tasks 006/010/007 reuse it.

## Acceptance Criteria
- All requirements have passing tests
