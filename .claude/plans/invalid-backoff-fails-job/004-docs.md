# Task 004: Document invalid backoff behaviour

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Update the Retry Backoff section of the queue docs page to say what happens on an invalid value: `queue.backoff` stops `queue:work` at startup, an invalid job `$backoff` fails that job into failed_jobs with both errors.

## Context
- Related files: packages/docs-markdown/docs/packages/queue.md, packages/queue/README.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] Retry Backoff section documents invalid config and invalid job backoff behaviour
- [x] README remains a slim pointer (no change needed unless it mentions backoff)

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
