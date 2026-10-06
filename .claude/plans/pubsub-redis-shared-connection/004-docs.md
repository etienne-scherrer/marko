# Task 004: Document the shared-connection model

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Document that one `RedisSubscriber` (shared per process) holds one Redis connection for all its subscriptions, rewrite the broadcasting-amphp `maxclients` sizing paragraph, and slim the package README to the pointer format.

## Context
- Related files: packages/docs-markdown/docs/packages/pubsub-redis.md, packages/docs-markdown/docs/packages/broadcasting-amphp.md, packages/pubsub-redis/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `pubsub-redis docs page has a Connections section describing the shared subscriber connection, cancel and reconnect behaviour`
- [x] `broadcasting-amphp docs state one Redis connection per SSE process`
- [x] `README follows the slim pointer format and nothing it said is lost from the docs page`

- [x] `pubsub-redis docs page warns that every subscription must be iterated promptly, because one unconsumed subscription (or a multi-channel subscription, whose channels are drained one after another) stalls delivery for all subscriptions in the process`

## Acceptance Criteria
- Docs match the implementation

## Implementation Notes
These are amphp `RedisSubscriber` facts (vendor/amphp/redis/src/RedisSubscriber.php). Describe them accurately:
- The connection opens on the first subscription. It does NOT stay closed when idle: after the last cancel amphp closes the socket, and its run loop reconnects at once with no subscriptions. So a process that has subscribed keeps one connection for its lifetime. Do not claim the connection is released when idle.
- After a dropped connection, amphp reconnects and re-subscribes every channel and pattern. Messages published while it was disconnected are lost (Redis pub/sub is at-most-once).
- If the reconnect itself fails (Redis down), every open subscription ends with an error at once. The next `subscribe()` starts a fresh connection.
- Cancelling a subscription unsubscribes its channel only when no other subscription on the same channel remains.
- Delivery uses backpressure: each message waits until its consumer reads it, and the shared connection reads nothing else meanwhile.

**Outcome:** Added `## Connections` (with a caution about unread subscriptions) to the pubsub-redis page, documented `createAmphpSubscriber()`, `SharedAmphpRedisSubscriber` and the README-only facts, rewrote the broadcasting-amphp sizing paragraph, and slimmed the README to the pointer format (PackageScaffoldingTest updated to match).
