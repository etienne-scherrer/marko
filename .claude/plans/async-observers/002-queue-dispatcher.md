# Task 002: Queue implementation of the async observer dispatcher

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `Marko\Queue\QueueAsyncObserverDispatcher` that pushes an `AsyncObserverJob` with the event serialized and wrapped in a `JobEnvelope`, bind it in queue's module.php, and let `SyncQueue` prepare container-aware jobs like the Worker does.

## Context
- Related files: packages/queue/src/AsyncObserverJob.php, packages/queue/src/JobEnvelope.php, packages/queue/module.php, packages/queue-sync/src/SyncQueue.php
- Do not touch packages/queue/src/Worker.php (#162)

## Requirements (Test Descriptions)
- [x] `it pushes an AsyncObserverJob carrying the observer class`
- [x] `it wraps the serialized event in a signed job envelope`
- [x] `it throws a SerializationException naming the observer when the event cannot be serialized`
- [x] `it binds AsyncObserverDispatcherInterface to QueueAsyncObserverDispatcher in module.php`
- [x] `it gives container-aware jobs the container and job envelope before handling them`
- [x] `it builds a SyncQueue with the container and job envelope from SyncQueueFactory`
- [x] `it clears the container and envelope from AsyncObserverJob after handle() so a failed job can still be serialized`

## Notes
- Implement `AsyncObserverDispatcherInterface::dispatch(string $observerClass, Event $event): void` (contract from 001).
- Add `SerializationException::unserializableEvent(string $observerClass, string $eventClass, Throwable $previous)`.
- `SyncQueue` gets constructor-promoted `ContainerInterface` + `JobEnvelope`. Update every `new SyncQueue()` site: `SyncQueueFactory` (inject both), `packages/queue-sync/tests/{SyncQueueTest,ModuleTest,Unit/SyncQueueFactoryTest}.php`, `packages/queue/tests/Feature/IntegrationTest.php`. Without the envelope, `AsyncObserverJob::handle()` would `unserialize()` the signed string and hand the observer `false`.
- `Worker::handleFailedJob()` serializes the job (`serialize($this)`) after `setContainer()`, which fails on the Container's Closures/PDO and crashes the worker on a job's final failure. Worker.php is off-limits (#162), so `AsyncObserverJob::handle()` must null `$container`/`$jobEnvelope` in a `finally` block.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
