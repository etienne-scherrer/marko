# Task 006: Docs pages and clock.md adopter list

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Each touched package's docs page mentions that it reads time through `ClockInterface` (and shows a `FakeClock` example where time is user-visible, e.g. SSE timeout). `clock.md` lists the new adopting packages. READMEs stay slim pointers (no change unless they contradict the new constructor).

## Context
- Related files: packages/docs-markdown/docs/packages/{notification,notification-database,broadcasting-amphp,broadcasting-mercure,broadcasting-pusher,media,sse,clock}.md
- Patterns to follow: docs/DOCS-STANDARDS.md; clock.md "Packages Using the Clock"

## Requirements (Test Descriptions)
- [x] each touched package page mentions ClockInterface
- [x] sse.md shows freezing time with FakeClock for heartbeat/timeout
- [x] clock.md lists notification, notification-database, broadcasting-amphp, broadcasting-mercure, broadcasting-pusher, media and sse

## Acceptance Criteria
- Docs follow DOCS-STANDARDS
- API reference signatures match the code

## Implementation Notes
(Left blank - filled in by programmer during implementation)
