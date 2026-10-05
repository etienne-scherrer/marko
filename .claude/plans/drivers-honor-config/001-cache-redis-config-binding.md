# Task 001: cache-redis config binding and loud connection failure

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `config/cache-redis.php`, bind `RedisConnection` through a closure that reads it, mark it a singleton, and convert refused connections into a `RedisConnectionException` that names host/port and the config file.

## Context
- Related files: packages/cache-redis/module.php, packages/cache-redis/src/RedisConnection.php, packages/cache-redis/tests/ModuleTest.php, packages/cache-redis/tests/RedisConnectionTest.php
- Patterns to follow: packages/pubsub-pgsql/module.php

## Requirements (Test Descriptions)
- [x] `it ships a cache-redis config file with connection defaults`
- [x] `it resolves RedisConnection with values from cache-redis config`
- [x] `it treats an empty cache-redis password as no password`
- [x] `it resolves the same RedisConnection instance twice`
- [x] `it throws RedisConnectionException naming host, port and config file when the connection is refused`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- `RedisConnection` constructor unchanged

## Implementation Notes
The default `createClient()` calls a new protected `connect()` that opens the Predis connection eagerly and converts `CommunicationException` into `RedisConnectionException`. Keeping the connect inside `createClient()` means existing test doubles that override `createClient()` (including the ones in RedisCacheDriverTest, which #165 owns) are unaffected.
