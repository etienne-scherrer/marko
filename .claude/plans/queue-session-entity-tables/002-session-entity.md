# Task 002: Session entity

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a `DatabaseSession` entity (`sessions`: `id VARCHAR(128)` PK, `payload TEXT`, `last_activity INT`) to marko/session-database so `db:migrate` creates the table.

## Context
- Related files: packages/session-database/src/Handler/DatabaseSessionHandler.php (unchanged)
- Patterns to follow: Task 001 entities

## Requirements (Test Descriptions)
- [ ] `it maps DatabaseSession to the sessions table with the documented columns`
- [ ] `it generates the documented sessions DDL on MySQL and PostgreSQL`
- [ ] `it lives in src/Entity so db:migrate discovers it`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
