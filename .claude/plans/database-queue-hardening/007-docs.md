# Task 007: Docs pages and READMEs

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Update `packages/docs-markdown/docs/packages/queue.md` and `queue-database.md` (worker binding, retry semantics, envelope format, config keys, nullable maxAttempts) and `concepts/events.md` if needed; keep READMEs slim per DOCS-STANDARDS.

## Requirements (Test Descriptions)
- [x] `docs describe the WorkerInterface binding`
- [x] `docs describe attempt counting including crashed reservations`
- [x] `docs describe the b64 envelope format and legacy compatibility`
- [x] `docs describe queue.queue, queue.retry_after and queue.max_attempts and the nullable job maxAttempts`

## Acceptance Criteria
- Docs accurate to the implementation

## Implementation Notes
