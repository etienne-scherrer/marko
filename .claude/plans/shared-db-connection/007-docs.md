# Task 007: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Document in packages/docs-markdown/docs/packages/database.md that the connection and hydrator are shared per request (per worker under long-running runtimes), how to inject `TransactionInterface`, and that apps should depend on interfaces rather than the concrete connection classes. Update the ResettableInterface implementors list in .claude/architecture.md.

## Requirements (Test Descriptions)
- [x] Docs page documents injecting TransactionInterface
- [x] Docs page states the connection is shared per request/worker
- [x] Architecture doc lists the driver connections as ResettableInterface implementors

## Acceptance Criteria
- Follows docs/DOCS-STANDARDS.md

## Implementation Notes
(Left blank - filled in by programmer during implementation)
