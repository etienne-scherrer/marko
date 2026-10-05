# Plan: Drivers Honor Config

## Created
2026-10-05

## Status
completed

## Objective
Make the Redis cache, Redis pub/sub and RabbitMQ queue drivers read their connection settings from config files through explicit closure bindings, share their connection objects as singletons, and fail loudly with config-aware messages when a connection is refused.

## Related Issues
Closes #166

## Discovery Notes
- The container never injects config into scalar constructor parameters, so connection classes with scalar defaults are silently autowired with localhost defaults.
- `packages/pubsub-pgsql/module.php` already shows the correct closure-factory pattern.
- `cache-redis` has no config file; its docs document a hand-written boot rebind workaround.
- `pubsub-redis` ships `config/pubsub-redis.php` but nothing reads it; `RedisPubSubConnection` also ignores `password`/`database` when building the amphp client, and its `prefix` property is unused (publisher/subscriber read `pubsub.prefix` via `PubSubConfig`).
- `queue-rabbitmq` has no config file; `ExchangeConfig` has required scalar params, so `QueueInterface` cannot resolve at all; `defaultQueue` ignores `queue.queue`.
- #160 (env-var handling) is still open, so new config files use `$_ENV` like the existing ones.
- Hotspot: #165 edits `cache-redis` driver code; this plan confines cache-redis changes to `RedisConnection`, `module.php`, config and docs.
- Audit (reflection over every `packages/*/src` constructor with defaulted scalar params): the only other autowired driver with the same problem is `marko/queue-database` (`DatabaseQueue::$defaultQueue`/`$retryAfter` ignore `queue.queue`/`queue.retry_after`). Its `module.php` is owned by #161, so it is reported as a follow-up rather than changed here.

## Scope

### In Scope
- `cache-redis`: `config/cache-redis.php`, closure binding + singleton for `RedisConnection`, loud connection failure
- `pubsub-redis`: closure binding + singleton for `RedisPubSubConnection` reading `pubsub-redis.*`, prefix from `pubsub.prefix`, honor password/database in the client config
- `queue-rabbitmq`: `config/queue-rabbitmq.php`, closure bindings for `RabbitmqConnection` (singleton), `ExchangeConfig`, `QueueInterface` (default queue from `queue.queue`), loud connection failure
- Docs pages and READMEs for the three packages

### Out of Scope
- A container-level `#[Config]` parameter injection feature
- `queue-database` default queue / retry_after wiring (owned by #161's module.php changes)
- `RedisCacheDriver` changes (owned by #165)

## Success Criteria
- [x] Module tests: with a `FakeConfigRepository` holding non-default values, the resolved connection carries those values
- [x] Resolving the connection twice returns the same instance
- [x] `QueueInterface` resolves with only queue + queue-rabbitmq + config
- [x] A refused connection error names host and port and points to the config file
- [x] Docs pages updated with config keys and env var tables
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | cache-redis config binding and loud connection failure | - | completed |
| 002 | pubsub-redis config binding | - | completed |
| 003 | queue-rabbitmq config, bindings and loud connection failure | - | completed |
| 004 | Docs pages and READMEs | 001, 002, 003 | completed |

## Architecture Notes
- Follow the `pubsub-pgsql` closure-factory pattern; mark connection classes as list-style singletons.
- Keep connection constructors source-compatible; factories only supply values.
- Exceptions extend `MarkoException` with message/context/suggestion.

## Risks & Mitigations
- Docs merge conflict with #165 on `cache-redis.md`: keep edits confined to the Configuration section.
- Eager Predis connect in `client()` changes laziness timing: the connect happens on the first `client()` call, which is when the first command would have connected anyway.
