# Task 005: SQLite-backed regression + container-resolution tests

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
End-to-end regression tests running the real migrations against an in-memory SQLite `ConnectionInterface` fixture, and a container-resolution test that wires only queue + queue-database module bindings and runs `queue:work --once`.

## Context
- Related files: packages/queue-database/tests/Feature/*, packages/queue-database/tests/Fixtures/*

## Requirements (Test Descriptions)
- [x] `it attempts an always-failing job exactly maxAttempts times then moves it to failed_jobs`
- [x] `it removes the exhausted job from the jobs table`
- [x] `it counts an expired reservation as an attempt and eventually fails the job`
- [x] `it honours a changed queue.max_attempts end to end`
- [x] `it honours a changed queue.retry_after when reclaiming reservations`
- [x] `it pushes to and pops from the configured queue.queue name`
- [x] `it resolves queue:work from the container with only queue and queue-database bindings and runs once`
- [x] `it reports the real interface name for an unbound non-driver queue interface`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
