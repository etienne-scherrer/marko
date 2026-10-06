# Task 006: Docs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document the `ContainerAwareJobInterface` contract (`releaseContainer()`), what happens to injected services when a job fails, and what happens to a failed job whose payload cannot be serialized. Remove `SyncQueueFactory` from queue-sync docs; update notification and webhook docs.

## Context
- Related files: packages/docs-markdown/docs/packages/queue.md, queue-sync.md, notification.md, webhook.md
- Document the placeholder/refusal contract exactly as specified in task 002 (placeholder keys, row kept on refusal, `--all` exit code 1 when any skipped) and mark `releaseContainer()` as a breaking addition for third-party implementers.
- Follow docs/DOCS-STANDARDS.md; READMEs stay slim pointers (no change needed unless they mention changed classes)

## Requirements (Test Descriptions)
- [ ] `queue.md documents releaseContainer() and the failure path for injected services`
- [ ] `queue.md documents failed jobs whose payload cannot be serialized and how queue:retry treats them`
- [ ] `queue-sync.md no longer mentions SyncQueueFactory`
- [ ] `notification.md and webhook.md mention that the worker releases the container`

## Acceptance Criteria
- Docs accurate against the code

## Implementation Notes
(Left blank - filled in by programmer during implementation)
