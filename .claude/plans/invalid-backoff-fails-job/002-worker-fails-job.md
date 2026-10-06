# Task 002: Worker fails a job with an invalid backoff and keeps working

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
When a failed job has attempts left but its backoff is invalid, `handleFailedJob()` must not let `QueueException` escape. It stores the job in failed_jobs with the job's error and the backoff error, deletes it from the queue, and the worker carries on.

## Context
- Related files: packages/queue/src/Worker.php, packages/queue/tests/WorkerTest.php
- Patterns to follow: #218 placeholder-payload path in `handleFailedJob()`

## Requirements (Test Descriptions)
- [x] `it stores a job with an invalid backoff in failed jobs with the job error and the backoff error`
- [x] `it deletes a job with an invalid backoff from the queue instead of releasing it`
- [x] `it processes the next job after failing a job with an invalid backoff`
- [x] `it fails the job when the queue.backoff config is invalid instead of stopping the worker`
- [x] `it still propagates a QueueException thrown by the driver's release()` (only `backoffFor()` is guarded; a driver failure must not be recorded as an invalid backoff)
- [x] `it records the job error, the backoff error and the serialization note when an invalid-backoff job cannot be serialized`
- [x] existing valid-backoff tests (fixed, list, null curve, release delay) still pass unchanged

## Acceptance Criteria
- All requirements have passing tests
- `backoffFor()` still throws `QueueException` for invalid values (public contract)

## Implementation Notes
Compute the delay in its own `try { $delay = $this->backoffFor($job); } catch (QueueException $backoffError) { ... }` and call `$this->queue->release($job->id, $delay)` OUTSIDE that try, so driver exceptions from `release()` are not swallowed as backoff errors. Extracted `storeFailedJob()`; the backoff branch appends "Job could not be retried: <message> <context>" to the exception text.
