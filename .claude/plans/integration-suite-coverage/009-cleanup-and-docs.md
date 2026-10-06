# Task 009: Remove KnownGapsTest, docs

**Status**: completed
**Depends on**: 008
**Retry count**: 0

## Description
Delete `KnownGapsTest.php` (no rows left), make sure no fixture comment still describes a fixed bug, and document the env vars and how to run each group locally in `.claude/testing.md`. Update `CLAUDE.md` and `.claude/pr-review-process.md` to describe the Integration job and the pending required-check decision.

## Context
- Related files: `.claude/testing.md`, `CLAUDE.md`, `.claude/pr-review-process.md`

## Requirements (Test Descriptions)
- [x] `it keeps no todo rows for merged tickets in the integration suite`
- [x] docs list MARKO_TEST_PGSQL_*, MARKO_TEST_MYSQL_*, DB_*, REDIS_* and MARKO_INTEGRATION_REQUIRED

## Acceptance Criteria
- `composer ci` green

## Implementation Notes
