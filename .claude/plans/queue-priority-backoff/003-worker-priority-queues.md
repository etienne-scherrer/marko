# Task 003: Worker priority queue list and concrete failed-job queue

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Change `WorkerInterface::work()` to take a priority-ordered `list<string>` of queues (null = config default queue). Each iteration pops queues in order and processes the first job found; the worker sleeps only when all are empty.

## Context
- Related files: packages/queue/src/WorkerInterface.php, Worker.php, tests/WorkerTest.php, tests/WorkerInterfaceTest.php, src/Command/WorkCommand.php, tests/Command/WorkCommandTest.php
- Patterns to follow: existing worker loop

## Keep the suite green (fixed by devil's advocate)
- `tests/Command/WorkCommandTest.php` has 6 anonymous `implements WorkerInterface` stubs with `work(?string $queue, ...)`; they become fatal once the signature changes. Update them to `work(?array $queues = null, bool $once = false, int $sleep = 3)` and their captured values in this task.
- `WorkCommand` calls `work(queue: $queue, ...)`; renaming the parameter throws "Unknown named parameter". Minimal adaptation here: pass `queues: $queue === null ? null : [$queue]`. Comma parsing stays in task 004.

## Semantics
- Strict priority: every iteration pops from the first queue again; the first non-null job is processed, then the loop restarts at queue 0.
- `$queues === null` calls `pop(null)` exactly once per iteration (driver default, unchanged behaviour) and records `$this->config->queue()` for failed jobs.
- Validate the list before the loop: empty list or non-string/blank entries throw `QueueException`.
- Pass the concrete popped queue name to `handleFailedJob()`.

## Requirements (Test Descriptions)
- [x] `it processes jobs on the high queue before jobs on the low queue`
- [x] `it sleeps only when every listed queue is empty`
- [x] `it processes at most one job across all queues with once`
- [x] `it records the concrete queue a failed job was popped from`
- [x] `it records the config default queue when no queues are given`
- [x] `it throws QueueException when given an empty queue list`
- [x] `it returns to the highest priority queue after each processed job`
- [x] `it pops the driver default queue when queues is null`

## Acceptance Criteria
- All requirements have passing tests
- Single-queue/default behaviour unchanged

## Implementation Notes
(Left blank - filled in by programmer during implementation)
