# Task 003: End-to-end async observer tests

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Prove the full path: real `EventDispatcher` + `QueueAsyncObserverDispatcher` with FakeQueue, the real `Worker`, and `SyncQueue`.

## Context
- Related files: packages/queue/tests/Feature/

## Requirements (Test Descriptions)
- [x] `it pushes exactly one AsyncObserverJob and does not run the observer inline`
- [x] `it runs the pushed job through the real Worker and invokes the observer with an equal event`
- [x] `it runs async observers through the SyncQueue path when queue-sync is installed`
- [x] `it pushes a job for an async observer instead of running it inline` (integration, `->issue(163)`)

## Notes
- `FakeQueue::pop()` returns the same object that was pushed. The Worker round trip must serialize the job (`$job->serialize()` / `AsyncObserverJob::unserialize()`, or a queue that stores serialized payloads) so it really proves the event survives serialization.
- Put the SyncQueue test in `packages/queue-sync/tests` (queue-sync depends on queue).
- Flip the `#163` todo in `tests/Integration/App/KnownGapsTest.php` into a real test in `tests/Integration/App/ServicesTest.php`, tagged `->issue(163)` so `HarnessTest` still finds it. Dispatch `BookPublished` via `EventDispatcherInterface`, assert there is no marker file and one `jobs` row, then run `queue:work --once` and assert the marker is written. Update the `RecordBookPublished` docblock. See `.claude/testing.md` "Flipping a todo". Runs under `composer test:integration` (docker).

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
