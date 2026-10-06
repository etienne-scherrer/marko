# Task 005: Documentation

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Note in the migrations section of `packages/docs-markdown/docs/packages/database.md` that type, default and nullability changes are generated, and reversed by `db:rollback`. `database-pgsql.md` does not document the old limitation; leave it unless needed.

## Requirements (Test Descriptions)
- [x] `docs describe generated and reversed column modifications`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
(Left blank - filled in by programmer during implementation)
