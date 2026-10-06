# Task 006: docs — package pages and clock.md adopter list

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Each touched package's docs page says it reads time through `ClockInterface` (with a `FakeClock` example where time is user-visible), updated constructor signatures are reflected, and `clock.md` lists the new adopters.

## Context
- Related files: packages/docs-markdown/docs/packages/{authentication,admin-auth,errors,errors-simple,errors-advanced,log,log-file,testing,clock}.md, docs/DOCS-STANDARDS.md
- Also: packages/errors/README.md (line ~19 `fromThrowable` example), packages/docs-markdown/docs/packages/roadrunner-state-leaks.md (line ~51 says `RememberTokenManager` holds "only the configured remember lifetime"; it now also holds the shared clock singleton, and still does not leak)
- Specific spots: errors.md `fromThrowable` example (~71) and signature (~199); errors-simple.md `new SimpleErrorHandler(` (~124) and `fromThrowable` (~159); errors-advanced.md module closure example (~81); log-file.md constructor table (~117-123) needs a `$clock` row, and `$rotation` becomes nullable; admin-auth.md should note that event timestamps come from the repository and are UTC

## Requirements (Test Descriptions)
- [ ] `it documents ClockInterface use on each touched package page`
- [ ] `it lists the new adopters in clock.md`

## Acceptance Criteria
- Docs match the shipped API

## Implementation Notes
