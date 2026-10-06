# Plan: pubsub-redis Shared Subscriber Connection

## Created
2026-10-05

## Status
completed

## Objective
Make `marko/pubsub-redis` multiplex every `subscribe()`/`psubscribe()` from one `RedisSubscriber` over a single Redis connection, instead of opening a new connection per call, and document the shared-connection model.

## Related Issues
Closes #229

## Discovery Notes
- `RedisSubscriber::subscribe()`/`psubscribe()` call `createAmphpSubscriber()` per call, and each `DefaultAmphpRedisSubscriber` wraps a fresh `Amp\Redis\RedisSubscriber`, so each call opens one Redis connection.
- `Amp\Redis\RedisSubscriber` already multiplexes channels and patterns over one connection, unsubscribes a channel when its last queue is released, and re-subscribes every channel and pattern after a reconnect.
- `marko/pubsub-pgsql` memoises one connection and every `LISTEN` goes over it; it is the reference.
- `RedisSubscriber` is `readonly class`; `.claude/sibling-modules.md` requires sibling class modifiers to match (`PgSqlSubscriber` is `readonly class`, enforced by `SiblingConsistencyTest`). So instead of dropping `readonly`, the subscriber holds a lazily-initialised holder object (the ticket's second option).
- `module.php` binds `SubscriberInterface` but does not share it, so each container resolution built its own subscriber. Marking it a singleton makes the "one connection per process" claim hold across resolutions.
- Live tests follow the `integration-services` pattern: skip with a reason when Redis is unreachable, fail when `MARKO_INTEGRATION_REQUIRED` is set. Moving `broadcasting-amphp`'s `RedisLiveTest` into the group is left to #226.

## Scope

### In Scope
- `SharedAmphpRedisSubscriber`: lazy holder that calls a factory once and delegates to the result
- `RedisSubscriber` routes every subscription through one shared amphp subscriber; `createAmphpSubscriber()` stays the protected seam, called once
- `SubscriberInterface` shared as a singleton in `module.php`
- Live Redis tests (`integration-services`): 500 channels share one connection, routing, per-channel cancel, recovery after a connection drop
- Docs: pubsub-redis page documents the shared connection; broadcasting-amphp sizing paragraph rewritten; README slimmed to the pointer format

### Out of Scope
- Moving `packages/broadcasting-amphp/tests/Feature/RedisLiveTest.php` into `integration-services` (#226)
- Changes to `SubscriberInterface`/`Subscription`
- (Moved into scope as task 005 after the post-plan review: a multi-channel `RedisSubscription` drained its channels one after another, which on a shared connection would stall every subscription in the process.)

## Success Criteria
- [x] N `subscribe()` calls on one `RedisSubscriber` call `createAmphpSubscriber()` exactly once
- [x] 500 distinct channel subscriptions use one Redis client connection; messages route correctly; cancelling one channel stops only that channel
- [x] After a Redis connection drop, all remaining subscriptions receive messages again
- [x] broadcasting-amphp tests and its live Redis test pass
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Share one amphp subscriber per RedisSubscriber | - | completed |
| 002 | Share RedisSubscriber as a container singleton | - | completed |
| 003 | Live Redis tests for the shared connection | 001 | completed |
| 004 | Document the shared-connection model | 001, 002 | completed |
| 005 | Read a multi-channel subscription's channels at once | 001 | completed |

## Architecture Notes
- Keep `RedisSubscriber` a `readonly class` (sibling parity with `PgSqlSubscriber`); mutable lazy state lives in `SharedAmphpRedisSubscriber`, which is not readonly.
- The holder takes a `Closure` factory so `createAmphpSubscriber()` remains the overridable seam.

## Risks & Mitigations
- Live tests racing the async SUBSCRIBE: poll `PUBSUB NUMSUB` until the channel is active before publishing.
- Other Redis clients and parallel workers: use a unique channel prefix per test, find the subscriber's client by diffing `CLIENT LIST TYPE pubsub` ids, and drop it with `CLIENT KILL ID`, never `CLIENT KILL TYPE pubsub`.
- Shared-connection backpressure: amphp's read loop waits for each message to be consumed, so one unconsumed subscription stalls every channel in the process. Live tests consume each subscription in its own fiber with timeouts, and the docs warn consumers about this (tasks 003, 004).
- A factory failure (`RedisException`) is not memoised, so the next subscription retries (task 001).
- amphp reconnects right after the last unsubscribe, so the process keeps one idle connection. The docs must not claim it is released when idle (task 004).
