# Task 005: Read a multi-channel subscription's channels at once

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Added after the post-plan review (deferred item). `RedisSubscription::getIterator()` drained its channels one after another. amphp waits for each message to be read before reading the next, so on the shared connection a message on a later channel of a multi-channel subscription would stall every subscription in the process until the earlier channel ended (which a live subscription never does). Merge the channels with `Amp\Pipeline\Pipeline::merge()`.

## Context
- Related files: packages/pubsub-redis/src/Driver/RedisSubscription.php, packages/pubsub-redis/tests/Driver/RedisSubscriptionTest.php, packages/pubsub-redis/composer.json (declare `amphp/pipeline`, now used directly)

## Requirements (Test Descriptions)
- [x] `it delivers a message on a later channel while an earlier channel is still open`
- [x] `it keeps per-channel order when reading several channels at once`
- [x] existing single-channel, pattern and cancel tests still pass

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
A single amphp subscription is still iterated directly (no extra fibers). Several are wrapped with `Pipeline::fromIterable()` and merged, so each channel is drained by its own fiber and keeps its own order.
