# Task 002: ScheduledTask::withoutOverlapping() + SchedulerException

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add overlap-prevention configuration to `ScheduledTask` and a stable mutex name derived from the expression and description. Closures have no stable identity, so a missing description must fail loudly.

## Context
- Related files: `packages/scheduler/src/ScheduledTask.php`, `packages/scheduler/src/Exceptions/`
- Patterns to follow: `InvalidCronExpressionException` (MarkoException with message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it does not prevent overlapping by default`
- [x] `it prevents overlapping with a default expiry of 1440 minutes`
- [x] `it accepts a custom overlap expiry in minutes`
- [x] `it rejects an overlap expiry below one minute`
- [x] `it builds a stable mutex name from the expression and description`
- [x] `it throws a helpful SchedulerException when withoutOverlapping is used without a description`

## Acceptance Criteria
- All requirements have passing tests
- Fluent API returns `self`

## Implementation Notes
(Left blank - filled in by programmer during implementation)
