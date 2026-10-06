# Plan: Queue Priority and Backoff

## Created
2026-10-05

## Status
completed

## Objective
Let one `queue:work` process drain a priority-ordered list of queues, and make the retry delay configurable per job and globally, without changing behaviour for existing single-queue apps.

## Related Issues
Closes #162

## Discovery Notes
- `Worker::work(?string $queue, ...)` pops a single queue; `handleFailedJob()` records `$queue ?? config default` and computes `2^attempts * 10` inline.
- `WorkCommand` already reads `--queue`/`--sleep` through `Input::getOption()` (landed with #161/#184), so ticket item 3 is already satisfied; only list parsing remains.
- Every driver already supports `pop(?string $queue)`; no driver change needed.
- `ConfigMerger` unsets a key when an app overrides it with `null` (see b30915fe), so a nullable config key must tolerate being absent.
- Only `Job` implements `JobInterface`; all `work()` callers use named `once:` only, except `WorkCommand` which passes `queue:`.

## Scope

### In Scope
- `WorkerInterface::work(?array $queues = null, bool $once = false, int $sleep = 3)` with `list<string>` priority order
- Priority popping, sleep only when all queues empty, `--once` across all queues
- Failed jobs record the concrete queue popped from
- `Job::$backoff` / `JobInterface::$backoff` (`int|list<int>|null`)
- `queue.backoff` config key + `QueueConfig::backoff()`
- `Worker::backoffFor(JobInterface $job): int`
- `queue:work --queue=a,b,c` parsing
- Docs page and README updates

### Out of Scope
- Job batches, dashboards, per-queue supervision, SIGTERM handling
- Driver changes

## Success Criteria
- [x] Worker processes `high` before `low` with `--queue=high,low`
- [x] Worker sleeps only when every listed queue is empty
- [x] Failed jobs record the concrete queue
- [x] Backoff: int, per-attempt list (last repeats), config default, unchanged default curve
- [x] `queue:work --queue=a,b --sleep=1` parsing test
- [x] Docs document priority lists and backoff; `config/queue.php` documents `backoff`
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Backoff on Job/JobInterface, queue.backoff config, QueueConfig::backoff() | - | completed |
| 002 | Worker::backoffFor() replaces the inline formula | 001 | completed |
| 003 | Worker priority queue list and concrete failed-job queue (also updates WorkCommand call + WorkCommandTest stubs to keep suite green) | 002 | completed |
| 004 | queue:work parses comma-separated queue list | 003 | completed |
| 005 | Docs page, config docs, README | 001, 002, 003, 004 | completed |

## Architecture Notes
- `queue.backoff` ships as `null`, meaning the existing exponential curve `2^attempts * 10`. A list cannot express an unbounded exponential curve, so shipping a list would change delays for jobs with more attempts than the list length. A missing key (removed by an app `null` override) is treated the same as `null`.
- Invalid backoff values (negative ints, empty list, non-int entries) throw `QueueException` loudly.
- An empty queue list throws `QueueException`.
- Backoff list index is `attempts - 1` (attempts is incremented before `handle()`), clamped to the last entry; the default curve keeps today's post-increment `2^attempts * 10`.
- `QueueConfig::backoff()` checks `has()` before `get()` and validates type only; `Worker::backoffFor()` owns value-shape validation for both job and config values.
- Priority is strict: each iteration restarts popping at the first queue. `null` queues → `pop(null)` (unchanged behaviour).

## Risks & Mitigations
- Signature change on `WorkerInterface::work()` breaks callers passing `queue:`: only `WorkCommand` does in-repo; documented in PR (pre-1.0).
- #163 edits the same package in parallel: changes kept to the files listed in the ticket.
