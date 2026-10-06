# Task 001: DestructiveCommandGuard opt-ins

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add two named, defaulted arguments to `DestructiveCommandGuard::check()`: `allowInProduction` (production is treated like staging: refused without `--force`, confirmed when interactive, runs non-interactively with `--force`) and `confirmInDevelopment` (development/testing ask for confirmation when interactive). Existing callers keep today's behaviour.

## Context
- Related files: `packages/database/src/Command/DestructiveCommandGuard.php`, `packages/database/tests/Command/DestructiveCommandGuardTest.php`

## Requirements (Test Descriptions)
- [x] `it refuses production without --force when production is allowed`
- [x] `it runs in production with --force when production is allowed and nobody can answer`
- [x] `it asks for confirmation in production with --force when production is allowed and interactive`
- [x] `it treats an unset environment as production when production is allowed`
- [x] `it asks for confirmation in development when confirmInDevelopment is set and interactive`
- [x] `it runs in development without asking when confirmInDevelopment is set and nobody can answer`
- [x] `it cancels in development when confirmInDevelopment is set and the answer is no`

## Acceptance Criteria
- Existing guard tests unchanged and passing

## Implementation Notes
