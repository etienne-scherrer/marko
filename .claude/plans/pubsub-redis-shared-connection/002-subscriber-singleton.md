# Task 002: Share RedisSubscriber as a container singleton

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Mark `SubscriberInterface` as a singleton in the pubsub-redis `module.php` so every consumer resolving it from the container shares the one subscriber, and therefore the one Redis connection.

## Context
- Related files: packages/pubsub-redis/module.php, packages/pubsub-redis/tests/ModuleBindingTest.php

## Requirements (Test Descriptions)
- [x] `it resolves SubscriberInterface to RedisSubscriber`
- [x] `it resolves the same SubscriberInterface instance twice`
- [x] `it keeps RedisPubSubConnection shared`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Added `SubscriberInterface::class` to the list-style `singletons`.
