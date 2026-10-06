# Task 004: Queue — failed_jobs, NUL payload, priority, backoff (#161, #162)

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Convert the #161 and #162 todos into `tests/Integration/App/QueueTest.php`. Configure a fixture backoff and update the PrivatePropertyJob docblock that still describes the #161 rejection.

## Context
- Related files: `Fixture/config/queue.php`, `Fixture/app/integration/src/Job/*`, `packages/queue/src/Worker.php`, `packages/queue-database/src/DatabaseQueue.php`

## Requirements (Test Descriptions)
- [x] `it moves a job that always throws to failed_jobs after max_attempts`
- [x] `it round-trips a job payload containing NUL bytes on postgres`
- [x] `it drains a higher-priority queue before a lower-priority one`
- [x] `it waits the configured backoff before retrying a failed job`

## Acceptance Criteria
- Tests pass against real Postgres

## Implementation Notes
- With a fixture backoff configured, a failed attempt pushes `available_at` into the future and `DatabaseQueue` only pops rows with `available_at <= now`. In the max_attempts test, rewind `available_at` to the past with direct SQL between `queue:work --once` runs. Do not lower the backoff to 0 to work around this.
- `DatabaseQueue` uses wall-clock time with second precision (`Y-m-d H:i:s`). Assert the backoff as a window: record time before and after the failing run and expect `available_at` between `before + backoff` and `after + backoff`. Pick a backoff that differs clearly from the `2^attempts * 10` default for the attempt under test (e.g. 7s; the attempt-1 default is 20s).
- Priority test: one `--once` run must process the "high" job and leave the "low" job in `jobs`.
