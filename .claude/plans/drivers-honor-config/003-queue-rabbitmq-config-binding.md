# Task 003: queue-rabbitmq config, bindings and loud connection failure

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `config/queue-rabbitmq.php`; bind `RabbitmqConnection` (singleton), `ExchangeConfig` and `QueueInterface` through closures so `QueueInterface` resolves with no hand-written bindings; convert refused connections into a `RabbitmqException`.

## Context
- Related files: packages/queue-rabbitmq/module.php, packages/queue-rabbitmq/src/RabbitmqConnection.php, packages/queue-rabbitmq/src/Exchange/ExchangeConfig.php, packages/queue/src/QueueConfig.php
- Patterns to follow: packages/pubsub-pgsql/module.php

## Requirements (Test Descriptions)
- [x] `it ships a queue-rabbitmq config file with connection and exchange defaults`
- [x] `it resolves RabbitmqConnection with values from queue-rabbitmq config`
- [x] `it passes a null tls config through as no TLS options`
- [x] `it resolves the same RabbitmqConnection instance twice`
- [x] `it resolves ExchangeConfig from queue-rabbitmq exchange config`
- [x] `it throws RabbitmqException for an unknown exchange type`
- [x] `it resolves QueueInterface with no hand-written bindings`
- [x] `it uses queue.queue as the default queue`
- [x] `it throws RabbitmqException naming host, port and config file when the connection is refused`

## Acceptance Criteria
- All requirements have passing tests
- `RabbitmqConnection` constructor unchanged

## Implementation Notes
`exchange.type` is parsed with `ExchangeType::tryFrom()` and an unknown value throws `RabbitmqException::invalidExchangeType()`. `channel()` converts any `AMQPExceptionInterface` from `createConnection()` into `RabbitmqException::connectionFailed()`; `tls` null or array maps to `tlsOptions`.
