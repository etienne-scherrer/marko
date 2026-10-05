# Task 002: pubsub-redis config binding

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Bind `RedisPubSubConnection` through a closure reading `pubsub-redis.*` (prefix from `pubsub.prefix`), make it a singleton, and make the amphp client/connector honor the configured password and database.

## Context
- Related files: packages/pubsub-redis/module.php, packages/pubsub-redis/src/RedisPubSubConnection.php, packages/pubsub-redis/config/pubsub-redis.php
- Patterns to follow: packages/pubsub-pgsql/module.php

## Requirements (Test Descriptions)
- [x] `it resolves RedisPubSubConnection with values from pubsub-redis config`
- [x] `it takes the connection prefix from pubsub.prefix`
- [x] `it resolves the same RedisPubSubConnection instance twice`
- [x] `it builds a redis config carrying host, port, password and database`

## Acceptance Criteria
- All requirements have passing tests
- `RedisPubSubConnection` constructor unchanged

## Implementation Notes
Added a protected `redisConfig()` that builds an amphp `RedisConfig` from the URI plus `withPassword()`/`withDatabase()`; both `createClient()` and `createConnector()` use it.
