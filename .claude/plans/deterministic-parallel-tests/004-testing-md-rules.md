# Task 004: Document the deterministic test rules in .claude/testing.md

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a contributor rule to `.claude/testing.md`: poll for conditions instead of asserting after a fixed delay, and give subprocess tests per-run temp dirs and the parent environment minus paratest variables.

## Context
- Related files: `.claude/testing.md` (Testing Principles section)

## Requirements (Test Descriptions)
- [x] testing.md states: poll for the condition with a timeout, never assert after a fixed delay
- [x] testing.md states: subprocess tests use per-run temp dirs and strip paratest variables
- [x] testing.md points to `Poll::until()` as the reference helper

## Acceptance Criteria
- Docs-only; no user-facing docs pages change

## Implementation Notes
Executed directly by the orchestrating agent in TDD order (task files are small and interdependent through one test file). See the PR for stress-test numbers.
