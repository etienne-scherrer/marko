# Task 001: Share one amphp subscriber per RedisSubscriber

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Route every `subscribe()`/`psubscribe()` on a `RedisSubscriber` through one `AmphpRedisSubscriberInterface`, created lazily on first use, so all subscriptions share one Redis connection.

## Context
- Related files: packages/pubsub-redis/src/Driver/RedisSubscriber.php, packages/pubsub-redis/src/Driver/SharedAmphpRedisSubscriber.php (new), packages/pubsub-redis/tests/Driver/RedisSubscriberTest.php, packages/pubsub-redis/tests/Driver/SharedAmphpRedisSubscriberTest.php (new)
- Patterns to follow: `PgSqlPubSubConnection::connection()` memoisation; keep `RedisSubscriber` `readonly class` (sibling parity)

## Requirements (Test Descriptions)
- [x] `it creates the amphp subscriber once across many subscribe calls`
- [x] `it creates the amphp subscriber once across subscribe and psubscribe calls`
- [x] `it does not create the amphp subscriber until the first subscription`
- [x] `it creates the inner subscriber on the first channel subscription only`
- [x] `it delegates pattern subscriptions to the inner subscriber`
- [x] `it does not call the factory until a subscription is made`
- [x] `it calls the factory again on the next subscription when the factory threw` (a `RedisException` from `connector()` must not be memoised)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
`SharedAmphpRedisSubscriber` implements `AmphpRedisSubscriberInterface`, takes a `Closure` factory and memoises its result. `RedisSubscriber` builds it in its constructor with `fn () => $this->createAmphpSubscriber()`, so the protected seam is unchanged and is called once.

The first three requirements already exist in `RedisSubscriberTest.php`. The remaining ones go in `SharedAmphpRedisSubscriberTest.php`. Assign the memoised instance only after the factory returns, so an exception leaves the holder empty and the next call retries. `SharedAmphpRedisSubscriber` must not be `readonly`. Give it a `@param Closure(): AmphpRedisSubscriberInterface $factory` docblock for PHPStan.
