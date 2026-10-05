# Task 001: Core contract and lazy async dispatch in EventDispatcher

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `AsyncObserverDispatcherInterface` to core, remove the queue dependency from `EventDispatcher`, and route async observers to a lazily resolved dispatcher, throwing a loud `EventException` when none is bound. Make `Container::has()` honour `instance()` registrations.

## Context
- Related files: packages/core/src/Event/EventDispatcher.php, packages/core/src/Exceptions/EventException.php, packages/core/src/Container/Container.php, packages/core/tests/Unit/Event/EventDispatcherTest.php
- Patterns to follow: EventException named constructors (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it hands async observers to the bound async observer dispatcher instead of running them inline`
- [x] `it throws an EventException with an install suggestion when an async observer fires and no dispatcher is bound`
- [x] `it does not resolve the async observer dispatcher when no async observer is dispatched`
- [x] `it resolves the async observer dispatcher once and reuses it across dispatches`
- [x] `it keeps priority order and propagation across a mix of sync and async observers`
- [x] `it reports instances registered via instance() from has()`
- [x] `it keeps packages/core free of Marko\Queue references`

## Contract (task 002 builds against this)
- `Marko\Core\Event\AsyncObserverDispatcherInterface::dispatch(string $observerClass, Event $event): void` (the signature the ticket specifies; a definition-based signature was considered for future per-observer queue names but rejected as speculative)
- `EventException::noAsyncObserverDispatcher(string $observerClass, string $eventClass): self`. The suggestion names `composer require marko/queue` plus a driver (e.g. `marko/queue-sync`, `marko/queue-database`).

## Existing tests to remove/rewrite
`packages/core/tests/Unit/Event/EventDispatcherTest.php` currently imports `Marko\Queue\AsyncObserverJob`/`QueueInterface` and `FakeQueue`. It has these tests: `accepts optional queue` (asserts a 3rd `queue` param), `queues async observers`, `executes sync observers immediately`, `falls back when no queue` (asserts inline execution, which now must throw). Remove or rewrite all of them against an in-test fake `AsyncObserverDispatcherInterface`, and remove every `Marko\Queue` import from core tests.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- Architecture test is scoped to the `Marko\Core` source namespace (`packages/core/src`)

## Implementation Notes
