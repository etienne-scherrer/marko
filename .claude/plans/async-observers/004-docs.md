# Task 004: Documentation

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Update events concept docs (error behaviour without a queue, serializability, sync driver) and the queue/queue-sync package docs (binding, container-aware jobs on the sync driver).

## Context
- Related files: packages/docs-markdown/docs/concepts/events.md, packages/docs-markdown/docs/packages/queue.md, packages/docs-markdown/docs/packages/queue-sync.md, packages/docs-markdown/docs/packages/core.md (line ~387 async comment)

## Requirements (Test Descriptions)
- [x] `events.md documents the missing-queue exception and event serializability`
- [x] `queue.md documents the AsyncObserverDispatcherInterface binding`
- [x] `docs note that an encryption key is required (envelope signing at dispatch), that marko/queue without a driver throws NoDriverException on the first async dispatch, and that under queue-sync an async observer's exception surfaces from dispatch() as JobFailedException`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
