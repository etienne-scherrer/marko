# Task 002: Worker serialization fallback and retry refusal

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
If a failed job still cannot be serialized (for example a closure property), the worker stores a placeholder payload recording the job class and the serialization error, records the error in the failed job's exception text, deletes the job from the queue, and keeps running. `queue:retry` refuses placeholder payloads with a clear message instead of crashing.

## Context
- Related files: packages/queue/src/Worker.php, packages/queue/src/Command/RetryCommand.php, packages/queue/src/Exceptions/SerializationException.php
- `FailedCommand::extractJobClass()` reads `class` from an array payload, so the placeholder shows the job class in `queue:failed`.
- Fallback contract (task 006 documents exactly this):
  - Wrap ONLY `$job->serialize()` in `try/catch (Throwable)` (closures throw a plain `Exception`). Do not catch around `JobEnvelope::wrap()` — its empty-signing-key `SerializationException` must stay loud.
  - Placeholder payload: `$this->jobEnvelope->wrap(serialize(['class' => $job::class, 'serialization_error' => $serializeError->getMessage()]))`.
  - `exception` text: original message + trace, then a line `Job payload could not be serialized: {message}` (or similar, asserted in tests).
  - Job is still deleted from the queue.
- Retry refusal contract:
  - After `verifyAndUnwrap` + `unserialize`, refuse anything that is `!instanceof JobInterface` (covers the placeholder array, `false`, and `__PHP_Incomplete_Class`). Use the placeholder's `class` / `serialization_error` in the message when present.
  - Refused rows are NOT deleted from `failed_jobs` and nothing is pushed.
  - Single ID: write the message, return 1.
  - `--all`: push and delete retryable rows, skip refused rows with a per-row message, report pushed and skipped counts, return 1 if any were skipped, else 0.
- Retry tests go in packages/queue/tests/Command/RetryCommandTest.php (task 004 builds on this file after this task).

## Requirements (Test Descriptions)
- [ ] `it records a job whose payload cannot be serialized as failed with the serialization error`
- [ ] `it stores the job class in the payload of a job that cannot be serialized`
- [ ] `it deletes a job that cannot be serialized from the queue and keeps working`
- [ ] `it refuses to retry a failed job whose payload could not be serialized`
- [ ] `it skips failed jobs whose payload could not be serialized when retrying all`
- [ ] `it keeps a failed job whose payload could not be serialized in the failed-job repository when retry refuses it`
- [ ] `it returns a failure exit code from retrying all when any failed job was skipped`
- [ ] `it still throws when the signing key is empty while storing a failed job`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
