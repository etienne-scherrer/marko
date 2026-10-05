# Task 005: Docs page, config docs, README

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Document priority lists and backoff in `packages/docs-markdown/docs/packages/queue.md`, the `backoff` key in `config/queue.php`, and update the queue guide/README where they describe `queue:work` or retries.

## Context
- Related files: packages/docs-markdown/docs/packages/queue.md, docs/guides/queues.md, packages/queue/README.md, packages/queue/config/queue.php
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs page documents --queue priority lists`
- [x] `docs page documents job and config backoff`
- [x] `config/queue.php comments the backoff key`

## Acceptance Criteria
- Docs accurate to shipped behaviour

## Implementation Notes
(Left blank - filled in by programmer during implementation)
