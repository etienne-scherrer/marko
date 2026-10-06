# Task 005: docs: package pages and clock.md

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Each package docs page mentions it reads time through `ClockInterface`, with a FakeClock freezing example for queue and scheduler; `clock.md` lists the adopting packages.

## Context
- packages/docs-markdown/docs/packages/{database,queue,queue-database,scheduler,clock}.md

## Requirements (Test Descriptions)
- [ ] `database.md describes the clock seam for timestamps` (rewrite line ~353, "override it in a repository to supply a different clock", to describe the injected `ClockInterface` first, with overriding `now()` as the secondary option)
- [ ] `queue.md and queue-database.md show freezing time with FakeClock`
- [ ] `scheduler.md shows freezing time with FakeClock`
- [ ] `clock.md lists database, queue, queue-database and scheduler`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
