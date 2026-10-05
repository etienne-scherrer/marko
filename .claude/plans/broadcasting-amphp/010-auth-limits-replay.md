# Task 010: Stream auth, limits and Last-Event-ID replay

**Status**: completed
**Depends on**: 003, 006
**Retry count**: 0

## Description
Extend `StreamRequestHandler` with private-channel token checks, connection limits and Last-Event-ID replay/reset. Integration tests reuse the task 006 harness.

## Requirements (Test Descriptions)
- [x] `it returns 403 for a private channel without, with expired, or with forged token`
- [x] `it returns 403 when the token does not list the requested private channel`
- [x] `it returns 403 for private channels when app_key is empty`
- [x] `it returns 503 with Retry-After over max_connections`
- [x] `it returns 503 with Retry-After over max_connections_per_ip`
- [x] `it uses X-Forwarded-For only from trusted_proxies`
- [x] `it returns 400 when more than the maximum channels are requested`
- [x] `it replays events after Last-Event-ID in order`
- [x] `it sends reset when the Last-Event-ID is too old`
- [x] `it sends reset when the Last-Event-ID is unknown`
- [x] `it does not duplicate or drop an event published while replaying`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Verify with `AmphpSignature::verify()`; `c` holds bare names, the URL uses `private-` (see `_plan.md` Shared contracts). Rejections happen before any hub join.
- Limit checks (global, per-IP, channel cap) happen before subscribing; release counters in the same `onClose` path as task 006. Channel cap is a class constant (e.g. 50) because each distinct channel can cost one Redis connection.
- Accept `Last-Event-ID` from the header, and also a `lastEventId` query parameter (EventSource cannot set headers on the first connect).
- Ordering to avoid gaps/duplicates: join the hub (buffer live frames) -> resolve replay from the ReplayBuffer -> write replay frames -> flush buffered live frames with sequence greater than the last replayed one.
- If a subscribe to pubsub throws (e.g. Redis down), respond 503 and log; do not leak the slot.
