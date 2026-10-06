# Task 005: Remove SyncQueueFactory

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`SyncQueueFactory` is unused (`queue-sync/module.php` binds `QueueInterface` to `SyncQueue` directly) and takes a `QueueConfig` it never reads. Delete it and its test, keeping the coverage its test provided on `SyncQueue` itself.

## Context
- Related files: packages/queue-sync/src/Factory/SyncQueueFactory.php, packages/queue-sync/tests/Unit/SyncQueueFactoryTest.php, packages/queue-sync/tests/SyncQueueTest.php, packages/queue-sync/tests/ModuleTest.php
- Depends on 001 because 001 also edits SyncQueueTest.php (fixture + release test).
- ModuleTest.php already has `module.php binds QueueInterface via factory` asserting a direct `SyncQueue` binding — rename it to the "without a factory" requirement below rather than adding a duplicate.
- Grep the repo (src, tests, docs) for `SyncQueueFactory` and remove every reference; delete the empty `src/Factory/` directory.

## Requirements (Test Descriptions)
- [ ] `it binds QueueInterface directly to SyncQueue without a factory`
- [ ] `it ships no SyncQueueFactory class`
- [ ] `it gives container-aware jobs the container and job envelope before handling them` (existing, kept)

## Acceptance Criteria
- Factory and its test deleted, no remaining references

## Implementation Notes
(Left blank - filled in by programmer during implementation)
