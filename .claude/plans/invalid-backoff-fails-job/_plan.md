# Plan: Invalid Backoff Fails the Job

## Created
2026-10-05

## Status
completed

## Objective
Stop one job with an invalid `$backoff` from crash-looping the worker fleet: record that job as failed (with both errors) and keep working, and refuse to start `queue:work` when the global `queue.backoff` config is invalid (option C of #234, the issue's recommendation).

## Related Issues
Closes #234

## Discovery Notes
- `Worker::backoffFor()` validates the job's `$backoff`, then the `queue.backoff` config, and throws `QueueException::invalidBackoff()`. It is called from `handleFailedJob()`, inside the loop's `catch (Throwable)`, so the exception escapes `work()`: the job is neither released nor failed and the original job error is lost.
- #218 (merged) restructured `handleFailedJob()`: unserializable jobs are stored with a placeholder payload. The new path reuses that storage code.
- `QueueConfig::backoff()` only checks the type (int/array/null), not the shape (negative int, empty or keyed list, non-int entries). Shape checks live in `Worker::resolveBackoff()`.
- `WorkCommand` only depends on `WorkerInterface`; `CliKernel` already returns exit code 1 for uncaught exceptions, but the command should refuse cleanly before printing "Processing jobs from queue...".
- The queue package has no logger dependency, and the container's constructor autowiring does not treat nullable params as optional, so adding `LoggerInterface` would force every queue app to install a log driver.

## Scope

### In Scope
- `BackoffValidator`: one place for backoff shape validation, used by `QueueConfig` and `Worker`
- `QueueConfig::backoff()` validates the full shape of `queue.backoff`
- `Worker::handleFailedJob()`: an invalid backoff on a retryable job stores the job in failed_jobs with both messages, deletes it from the queue, and the worker continues
- `queue:work` validates `queue.backoff` before starting; prints the error and exits 1 when invalid
- Docs: backoff section of `packages/docs-markdown/docs/packages/queue.md`

### Out of Scope
- Logging through `LoggerInterface` (see Architecture Notes)
- Validating per-job `$backoff` at startup (needs instantiating every job class)
- PSR-20 clock adoption (#221)

## Success Criteria
- [x] A job with an invalid `$backoff` that throws is stored in failed jobs with both messages, deleted from the queue, and the worker processes the next job
- [x] `queue:work` with an invalid `queue.backoff` refuses to start with the `invalidBackoff` message and a non-zero exit code
- [x] Valid backoffs behave exactly as today (fixed, list, null -> exponential)
- [x] Docs backoff section documents what happens on an invalid value
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | BackoffValidator and full queue.backoff validation in QueueConfig | - | completed |
| 002 | Worker fails a job with an invalid backoff and keeps working | 001 | completed |
| 003 | queue:work refuses to start with an invalid queue.backoff | 001 | completed |
| 004 | Document invalid backoff behaviour | 002, 003 | completed |

## Architecture Notes
- `BackoffValidator` is a plain class with no dependencies, injected into `Worker` and `QueueConfig` with a `new BackoffValidator()` default so existing constructions keep working and the container autowires it.
- The failed job's exception text is the job's own message and trace, followed by the backoff error (message and context), so both reasons survive in failed_jobs and `queue:failed`.
- No logger: the failed_jobs row is the record, as for every other failed job (the worker does not log ordinary job failures either).

## Risks & Mitigations
- Behaviour change for invalid config backoff at runtime: now caught at startup by `queue:work`; programmatic `Worker::work()` callers with an invalid config still get the job failed rather than a crash.
