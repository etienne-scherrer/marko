# Plan: Database Queue Hardening

## Created
2026-10-05

## Status
completed

## Objective
Make `marko/queue` + `marko/queue-database` production-usable: `queue:work` resolves out of the box, failing and crashing jobs reach `failed_jobs` after `max_attempts`, payloads survive PostgreSQL `TEXT`, and `config/queue.php` values are honoured.

## Related Issues
Closes #161

## Discovery Notes
- `WorkerInterface` is never bound; the container's interface fallback calls `Marko\Queue\Exceptions\NoDriverException::noDriverInstalled()` for every `Marko\Queue\*` interface, which reports "No queue driver installed" even when a driver is installed.
- `DatabaseQueue::release()` only clears `reserved_at`; the serialized payload keeps `attempts = 0`, so jobs loop forever. Crashed reservations are reclaimed without counting.
- `RabbitmqQueue::release()` already re-wraps the payload with an incremented count; mirror that.
- `JobEnvelope::wrap()` emits raw `serialize()` bytes (NUL bytes for private/protected props) into `TEXT` columns, which PostgreSQL rejects.
- `QueueConfig::retryAfter()`/`maxAttempts()` exist but are never called; `DatabaseQueue` is autowired with scalar defaults.
- Existing database-queue tests are mock-based; regression tests use a SQLite in-memory `ConnectionInterface` test fixture running the real migrations, and a PostgreSQL integration test gated on `DB_*` env vars (skipped with a clear reason when unavailable).
- `WorkCommand` option parsing is left alone (#162 reworks it).

## Scope

### In Scope
- Bind `WorkerInterface => Worker` in `packages/queue/module.php`
- `NoDriverException::noDriverInstalled(?string $interface = null)` names the real interface for non-driver contracts; `Container` passes `$id` when the factory accepts a parameter
- `DatabaseQueue`: `attempts` column counts reservations (source of truth); `release()` rewrites the payload with the synced count; `pop()` syncs the in-memory count and moves jobs exhausted by crashes to `failed_jobs`
- `JobEnvelope` emits `{hmac}.b64:{base64}`; legacy raw envelopes still verify
- `DatabaseQueue` built by a closure factory reading `QueueConfig` (`queue`, `retry_after`, `max_attempts`)
- `Job::$maxAttempts` becomes `?int` (null = config default); `Worker` falls back to `queue.max_attempts`
- Docs page + README updates

### Out of Scope
- Multi-queue priority / configurable backoff / `WorkCommand` option parsing (#162)
- Async observer wiring (#163)
- Column type changes
- A configurable `queue.database.table` key

## Success Criteria
- [x] `queue:work --once` resolves with only queue + queue-database bindings
- [x] Always-throwing job attempted exactly `maxAttempts` times, then in `failed_jobs` and gone from `jobs`
- [x] Expired reservation (simulated crash) counts as an attempt and ends in `failed_jobs`
- [x] PostgreSQL round-trip of a job with private/protected properties through push → pop and failed_jobs → queue:retry
- [x] Envelope: new format round-trip, legacy verifies, tampered base64 rejected
- [x] Config changes to `retry_after`, `max_attempts`, `queue` change behaviour
- [x] Unbound non-driver queue interface reports its real name
- [x] Docs updated
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | NUL-safe base64 JobEnvelope with legacy support | - | completed |
| 002 | Interface-aware NoDriverException + container call site | - | completed |
| 003 | Nullable Job maxAttempts, Worker config fallback, bind WorkerInterface | - | completed |
| 004 | DatabaseQueue attempt persistence, crash counting, config factory | 001, 003 | completed |
| 005 | SQLite-backed regression + container-resolution tests | 002, 003, 004 | completed |
| 006 | PostgreSQL integration round-trip test | 001, 004 | completed |
| 007 | Docs pages and READMEs | 001, 002, 003, 004 | completed |

## Architecture Notes
- The `attempts` column is the authoritative attempt count: the reserve `UPDATE` increments it, so it counts reservations (= attempts started). `release()` syncs the payload's `attempts` up to the column value. `pop()` syncs the unserialized job's count up to the pre-reservation column value, which covers reservations that were never released (crash/timeout).
- HMAC is computed over the whole payload segment after the `.` (for new envelopes that is `b64:{base64}`), so legacy and new formats share the verification path and the format marker is authenticated.
- Core keeps no dependency on `marko/queue`: the container only inspects the factory's parameter count via reflection.

## Risks & Mitigations
- `Job::$maxAttempts` type change breaks subclasses that redeclare `int $maxAttempts`: document `?int` in docs, mark PR `breaking`.
- `DatabaseQueue` constructor gains `FailedJobRepositoryInterface` and `maxAttempts`: it is only built via the module factory; tests updated.
