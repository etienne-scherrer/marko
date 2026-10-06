# Devil's Advocate Review: invalid-backoff-fails-job

## Critical (Must fix before building)
None. The plan is small and the targets (`Worker::backoffFor()`, `resolveBackoff()`, `handleFailedJob()`, `QueueConfig::backoff()`, `WorkCommand::execute()`) exist with the expected shapes.

## Important (Should fix before building)

1. **Task 002: the try/catch must wrap only `backoffFor()`, not `release()`.** If the worker wraps `$this->queue->release($job->id, $this->backoffFor($job))` in `catch (QueueException)`, a driver-level `QueueException` thrown by `release()` (DB/Redis/RabbitMQ failure) would be misreported as an invalid backoff and the job would be wrongly moved to failed_jobs. Compute the delay in its own try block, then call `release()` outside it. Add a test that a `QueueException` from `release()` still propagates.

2. **Task 002: invalid backoff combined with an unserializable job.** Both are handled in the shared storage path; the exception text must carry the job error, the backoff error, and the serialization note. Add a test so the extracted `storeFailedJob()` doesn't drop one of the appendices.

3. **Task 003: catch only `QueueException`, and print the context and suggestion as well as the message.** `MarkoException` exposes `getContext()` and `getSuggestion()`. Only the message would hide the actual reason ("every backoff list entry must be a non-negative int; got ..."). Catching `Throwable` would hide unrelated bugs. Validation must run before "Processing jobs from queue..." is printed. `WorkCommandTest.php` builds `WorkCommand` 12 times, and every one needs a `QueueConfig` (use `FakeConfigRepository`, because `QueueConfig` is a `readonly` class).

## Minor (Nice to address)
- `QueueConfig` is a `readonly class`. A promoted `private BackoffValidator $validator = new BackoffValidator()` is legal, but check that the container autowires the param (or uses the default) without a binding. It has no dependencies, so either works.
- On a job's final attempt `backoffFor()` is never called, so an invalid `$backoff` there produces a normal failed-job row with no backoff note. This is acceptable, but the docs (task 004) could say that validation only happens when a retry is needed.

## Questions for the Team
- Should `queue:status` or other commands also surface an invalid `queue.backoff`, or is `queue:work` the only fail-fast point?
