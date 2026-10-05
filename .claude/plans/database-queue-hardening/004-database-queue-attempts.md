# Task 004: DatabaseQueue attempt persistence, crash counting, config factory

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Make the `attempts` column authoritative: reserve increments it, `release()` rewrites payload + attempts + reserved_at + available_at in one UPDATE, `pop()` syncs the job's in-memory attempts with the pre-reservation column value and moves jobs exhausted by crashed reservations to `failed_jobs`. Replace the autowired binding with a closure factory reading `QueueConfig`.

## Context
- Related files: packages/queue-database/src/DatabaseQueue.php, packages/queue-database/module.php, packages/queue-database/tests/DatabaseQueueTest.php, ModuleTest.php
- Pattern: packages/pubsub-pgsql/module.php closure factory; RabbitmqQueue::release()

## Requirements (Test Descriptions)
- [x] `it increments the attempts column when reserving a job`
- [x] `it rewrites the payload with the incremented attempt count on release`
- [x] `it syncs the popped job attempts with reservations that were never released`
- [x] `it moves a job exhausted by crashed reservations to failed_jobs instead of returning it`
- [x] `module factory builds DatabaseQueue with queue.queue, queue.retry_after and queue.max_attempts`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
