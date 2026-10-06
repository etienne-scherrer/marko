# Plan: Failed Job Serialization

## Created
2026-10-05

## Status
completed

## Objective
Stop `queue:work` from crashing when a container-aware job fails for the last time: the worker releases injected runtime services before a job can be serialized into `failed_jobs`, and records a failed job even when its payload genuinely cannot be serialized. Remove the unused `SyncQueueFactory`.

## Related Issues
Closes #218

## Discovery Notes
- `Worker::work()` injects the container and `JobEnvelope` into every `ContainerAwareJobInterface` job, then `handleFailedJob()` calls `$job->serialize()` (plain `serialize($this)`). The container holds closures (and test containers are anonymous classes), so serialization throws and escapes the worker loop.
- `AsyncObserverJob` releases its services in a per-job `finally` (#209). `SendNotificationJob` and `DispatchWebhookJob` do not.
- `SyncQueue` injects the same services and never releases them.
- `SyncQueueFactory` is unused (module.php binds `QueueInterface => SyncQueue` directly) and takes an unused `QueueConfig`.
- `FailedCommand::extractJobClass()` already reads a `class` key from an unserialized array payload, so an array placeholder payload `['class' => ..., 'serialization_error' => ...]` displays the job class in `queue:failed`.
- `RetryCommand` assumes every payload unserializes to a `JobInterface`; it must refuse placeholder payloads loudly.
- `marko/queue` has no logger dependency. The serialization error is recorded in the failed job's `exception` text (the failure log users read with `queue:failed`), rather than adding a `marko/log` dependency.
- CHANGELOG is generated from PR labels: label the PR `breaking`.

## Scope

### In Scope
- `ContainerAwareJobInterface::releaseContainer(): void` (breaking)
- Worker calls `releaseContainer()` in a `finally` around `handle()`; `SyncQueue` does the same
- Implement `releaseContainer()` on `AsyncObserverJob` (drop its per-job `finally`), `SendNotificationJob`, `DispatchWebhookJob`
- Worker serialization fallback: placeholder payload, serialization error recorded, job deleted, worker keeps running
- `queue:retry` refuses placeholder payloads with a clear message (exit code 1; `--all` skips them and keeps going)
- Delete `SyncQueueFactory` and its test
- Docs: queue.md, queue-sync.md, notification.md, webhook.md

### Out of Scope
- `DatabaseQueue` changes (#224), backoff validation (#234), clock adoption (#221)
- Adding a logger dependency to `marko/queue`

## Success Criteria
- [ ] A container-aware job that always throws reaches maxAttempts, is stored as failed, and the worker keeps running
- [ ] Same for `SendNotificationJob` and `DispatchWebhookJob` with an unserializable container
- [ ] A job with a closure property is recorded as failed with a clear message; the worker does not crash
- [ ] `queue:retry` on a stored failed container-aware job runs it with a fresh container
- [ ] `SyncQueueFactory` removed
- [ ] Docs updated
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | releaseContainer() contract, worker/sync release, implementers | - | completed |
| 002 | Worker serialization fallback and retry refusal | 001 | completed |
| 003 | Notification and webhook final-failure worker tests | 001 | completed |
| 004 | queue:retry round trip with a fresh container | 001, 002 | completed |
| 005 | Remove SyncQueueFactory | 001 | completed |
| 006 | Docs | 001, 002, 003, 004, 005 | completed |

## Architecture Notes
- Explicit interface method, no `__serialize()` (no magic methods).
- Release runs in a `finally` that wraps only injection + `handle()`, so services are gone before `handleFailedJob()` serializes.
- Placeholder payload is still HMAC-wrapped so `queue:failed` and `queue:retry` verify it like any other row.
- Placeholder shape: `['class' => $job::class, 'serialization_error' => string]`. Only `$job->serialize()` is guarded; `JobEnvelope::wrap()` errors stay loud.
- `queue:retry` refuses any unwrapped payload that is not a `JobInterface`, keeps the row, and returns 1 (`--all` skips, reports skipped count, returns 1 if any skipped).
- Test jobs that must survive serialization are named classes; anonymous classes never serialize.
- Ordering: 005 after 001 (shared SyncQueueTest.php); 004 after 002 (shared RetryCommandTest.php / RetryCommand.php).

## Risks & Mitigations
- Third-party `ContainerAwareJobInterface` implementers break: documented, PR labelled `breaking`.
- Retry of a placeholder payload: refused loudly with the recorded class and error.
