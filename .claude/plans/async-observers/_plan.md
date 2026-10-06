# Plan: Async Observers

## Created
2026-10-05

## Status
completed

## Objective
Make `#[Observer(async: true)]` actually queue the observer through `marko/queue` (or fail loudly when no queue is installed), with core defining a contract and the queue package implementing it, so core no longer imports `Marko\Queue\*`.

## Related Issues
Closes #163

## Discovery Notes
- `EventDispatcher` (core) imports `Marko\Queue\AsyncObserverJob`/`QueueInterface` and takes an optional queue that `Application` never passes, so async observers always run inline.
- `AsyncObserverJob::handle()` verifies a `JobEnvelope` whenever the worker set one, but the dispatcher pushed raw serialized bytes, so a wired queue would fail with `signatureMismatch`.
- `SyncQueue::push()` calls `handle()` directly without giving container-aware jobs a container/envelope, so even with `queue-sync` an `AsyncObserverJob` would throw "called without a container".
- `Container::has()` only checks bindings and `class_exists()`, ignoring `instance()` registrations — an interface registered via `instance()` would wrongly look unbound.
- `Application` builds `new EventDispatcher($this->container, $this->observerRegistry)` already; after the change that call stays valid, so `Application.php` needs no edit (hotspot shared with #173).
- `Worker.php` is being edited by #162 in parallel; it is left untouched.

## Scope

### In Scope
- `Marko\Core\Event\AsyncObserverDispatcherInterface` contract
- `EventDispatcher`: drop queue imports/param, lazily resolve + memoize the async dispatcher, loud `EventException` when none bound
- `Container::has()` honours registered instances
- `Marko\Queue\QueueAsyncObserverDispatcher` (wraps serialized event in `JobEnvelope`), bound in `packages/queue/module.php`
- `SyncQueue` gives container-aware jobs the container + envelope (like `Worker`)
- Architecture test: no `Marko\Queue` references in `packages/core`
- Docs: concepts/events.md, packages/queue.md, packages/queue-sync.md

### Out of Scope
- Discovery-time validation in `ObserverDiscovery` (cannot check queue presence before module bindings are registered without resolving)
- Changes to `Worker.php` (#162) or `Application.php` (#173)

## Success Criteria
- [x] Async observer pushes exactly one `AsyncObserverJob` with FakeQueue and does not run inline
- [x] Round trip through real `Worker` + `JobEnvelope` invokes the observer with an equal event
- [x] With `SyncQueue`, observer runs via the queue path
- [x] No binding -> loud `EventException` with install suggestion
- [x] Priority and propagation hold across mixed sync/async observers
- [x] No `Marko\Queue` usage in core (architecture test)
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Core contract, Container::has fix, EventDispatcher lazy async dispatch | - | completed |
| 002 | QueueAsyncObserverDispatcher + module binding + SyncQueue/SyncQueueFactory container-aware support + AsyncObserverJob releases container after handle | 001 | completed |
| 003 | End-to-end tests (FakeQueue, serialized Worker round trip, SyncQueue) + flip #163 integration todo | 001, 002 | completed |
| 004 | Docs updates | 001, 002 | completed |

## Architecture Notes
- Dependency inversion: core owns the contract, queue implements it. Contract: `AsyncObserverDispatcherInterface::dispatch(string $observerClass, Event $event): void`.
- `Worker::handleFailedJob()` serializes the job after `setContainer()`. Worker can't be touched (#162), so `AsyncObserverJob` drops its container/envelope in a `finally` after `handle()`.
- Lazy: `has()`/`get()` are only called the first time an async observer is met; result memoized; `EventDispatcher` loses `readonly`.
- Serialization failures in events surface from `serialize()`; the queue dispatcher wraps them in `SerializationException` with the observer/event context.

## Risks & Mitigations
- Changing `Container::has()` semantics: run the core suite; behaviour only becomes PSR-11-correct.
- Removing the `EventDispatcher` queue param is breaking for hand-built dispatchers: internal in practice; called out in PR.
