# Task 001: releaseContainer() contract, worker/sync release, implementers

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `releaseContainer(): void` to `ContainerAwareJobInterface`. The worker and `SyncQueue` call it in a `finally` around `handle()`, so injected runtime services are gone on success and failure before a job is serialized. Implement it on every container-aware job.

## Context
- Related files: packages/queue/src/ContainerAwareJobInterface.php, packages/queue/src/Worker.php, packages/queue/src/AsyncObserverJob.php, packages/queue-sync/src/SyncQueue.php, packages/notification/src/Job/SendNotificationJob.php, packages/webhook/src/Jobs/DispatchWebhookJob.php, test jobs implementing the interface
- Patterns to follow: existing WorkerTest helpers (`SingleJobRecordingQueue`, `createNullWorkerContainer`)
- Every implementer must change in this task, including test fixtures: `ContainerAwareCaptureJob` in packages/queue-sync/tests/SyncQueueTest.php implements the interface and would fatal the whole queue-sync suite at file load.
- Dropping the `finally` in `AsyncObserverJob::handle()` breaks packages/queue/tests/AsyncObserverJobTest.php "clears the container and envelope from AsyncObserverJob after handle() so a failed job can still be serialized" (it asserts the second handle() throws "without a container"). Replace that test with the `releaseContainer()` requirement below; do not leave it failing.
- `releaseContainer()` must be idempotent and safe to call when nothing was injected.
- Worker structure: `try { try { inject; incrementAttempts; handle } finally { if container-aware: releaseContainer } delete } catch (Throwable) { handleFailedJob }`. SyncQueue: release in a `finally` around `handle()`, before `JobFailedException` propagates.
- Final-failure test jobs must be NAMED classes (top of the test file or tests/Fixtures) — anonymous classes cannot be serialized and would hide the bug behind task 002's fallback. Assert the stored payload unwraps and unserializes to the job class (not a placeholder array).

## Requirements (Test Descriptions)
- [ ] `it stores a container-aware job that always fails in the failed-job repository once it reaches maxAttempts`
- [ ] `it keeps working after a container-aware job fails for the last time`
- [ ] `it releases the container from a container-aware job after handle succeeds`
- [ ] `it releases the container from a container-aware job after handle throws`
- [ ] `it releases the container and envelope from AsyncObserverJob when releaseContainer is called`
- [ ] `it releases the container from a container-aware job after SyncQueue runs it`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
