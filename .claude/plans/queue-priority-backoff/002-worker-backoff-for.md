# Task 002: Worker::backoffFor() replaces the inline formula

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Move the retry-delay calculation into one public, testable `backoffFor(JobInterface $job): int` method that honours job backoff, then config backoff, then the existing exponential curve.

## Context
- Related files: packages/queue/src/Worker.php, packages/queue/tests/WorkerTest.php
- Patterns to follow: existing `handleFailedJob()`

## Requirements (Test Descriptions)
- [x] `it uses a fixed int job backoff for every attempt`
- [x] `it uses the list job backoff per attempt and repeats the last value`
- [x] `it falls back to the queue.backoff config when the job sets none`
- [x] `it keeps the 2^attempts * 10 curve when neither job nor config sets backoff`
- [x] `it throws QueueException for a negative or empty backoff`
- [x] `it throws QueueException for a backoff list with non-int entries or non-list keys`
- [x] `it validates config backoff values the same way as job backoff`
- [x] `it releases a failed job with the backoffFor delay`

## Semantics (fixed by devil's advocate)
- `$job->attempts` is already incremented before `handle()`, so the first failure sees `attempts = 1`.
- int: return it for every attempt. list: return `$list[min($job->attempts, count($list)) - 1]` (last value repeats).
- Neither set: `(int) pow(2, $job->attempts) * 10` using the same post-increment attempts as today (first retry = 20s) — do not change.
- Resolution order: `$job->backoff ?? $this->config->backoff() ?? curve`.
- Validation (single place, applies to job and config values): negative ints, empty list, non-int entries, and non-list arrays (`array_is_list()` false) throw `QueueException` with a message naming the job class or `queue.backoff`.

## Acceptance Criteria
- All requirements have passing tests
- Existing exponential-delay tests still pass unchanged

## Implementation Notes
(Left blank - filled in by programmer during implementation)
