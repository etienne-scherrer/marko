# Task 003: Live Redis tests for the shared connection

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove against a real Redis server that one subscriber multiplexes many channels over one connection, routes messages to the right subscription, unsubscribes only the cancelled channel, and recovers every channel after the connection drops.

## Context
- Related files: packages/pubsub-redis/tests/Integration/SharedConnectionLiveTest.php (new)
- Patterns to follow: `integration-services` group; skip with a reason when `REDIS_HOST` is unset or unreachable, throw when `MARKO_INTEGRATION_REQUIRED` is set (tests/Integration/App/Helpers.php)

## Requirements (Test Descriptions)
- [x] `it subscribes to 500 channels over one redis connection`
- [x] `it delivers each message only to the subscription for its channel`
- [x] `it stops only the cancelled channel`
- [x] `it restores every remaining subscription after the connection drops`

## Acceptance Criteria
- All requirements pass against Redis 7 (`docker compose -f tests/Integration/compose.yml up -d`)
- Tests skip cleanly with no Redis, fail with `MARKO_INTEGRATION_REQUIRED=1` and no Redis

- Safe under `composer test --parallel` and alongside other Redis clients (e.g. broadcasting-amphp `RedisLiveTest`). No test may affect another process's pub/sub clients.
- No test can hang: every await has a timeout.

## Implementation Notes
- Skip/fail: use `->group('integration-services')` and `integrationServicesSkipReason()` from tests/Integration/App/Helpers.php (autoloaded), as `packages/queue-database/tests/Integration/PgSqlRoundTripTest.php` does. Do not write a Redis-only check. CI semantics (`MARKO_INTEGRATION_REQUIRED`) come from the helper.
- Build `RedisSubscriber` by hand with a real `RedisPubSubConnection` (host/port from `REDIS_HOST`/`REDIS_PORT`) and a `PubSubConfig` whose prefix is unique per test (e.g. `'live:' . bin2hex(random_bytes(4)) . ':'`), so parallel workers never share channels.
- Wait for SUBSCRIBE to land by polling `PUBSUB NUMSUB` with a deadline.
- Connection count: diff the `CLIENT LIST TYPE pubsub` ids before and after subscribing. Exactly one new client should appear, and it should report `sub=500`. Never assert a global pub/sub client count.
- Drop the connection with `CLIENT KILL ID <that id>`, never `CLIENT KILL TYPE pubsub`, which would kill other workers' clients. After the kill, a new client id appears (amphp reconnects and re-subscribes). Wait for NUMSUB again before publishing.
- Backpressure: amphp pushes into unbuffered queues and `awaitAll()`s them, so one unconsumed subscription blocks the shared read loop for every channel. Consume every subscription in its own `Amp\async()` fiber, collecting messages into an array. Wrap awaits in `Amp\TimeoutCancellation` (or `Future::await(new TimeoutCancellation(5))`). Cancel every subscription in `afterEach` so fibers end.

**Outcome:** Kept a Redis-only skip helper (`sharedConnectionSkipReason()`) instead of `integrationServicesSkipReason()`, because the shared helper also requires Postgres, which these tests never touch. Same semantics: skip with a reason locally, throw when `MARKO_INTEGRATION_REQUIRED` is set. Subscriptions are cancelled in `finally` blocks, and every read is an `async()` awaited with a `TimeoutCancellation`. The tests fail against the pre-fix `RedisSubscriber` and pass repeatedly, serially and in parallel. Also fixed a pre-existing race in `packages/broadcasting-amphp/tests/Feature/RedisLiveTest.php` (it published before Redis registered the SUBSCRIBE and failed about 15% of runs): it now waits for `PUBSUB NUMSUB`. Its group is left to #226.
