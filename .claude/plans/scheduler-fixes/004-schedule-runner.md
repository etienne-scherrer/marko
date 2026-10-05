# Task 004: ScheduleRunner + schedule:run Exit Code and Overlap Handling

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Extract the run logic into a `ScheduleRunner` service used by `schedule:run` (and later `schedule:work`). Failures are reported and counted, remaining tasks still run, and the command exits `1` when any task failed. Tasks with `withoutOverlapping()` are skipped while their mutex is held and the mutex is always released.

## Context
- Related files: `packages/scheduler/src/Command/RunScheduleCommand.php`, `packages/scheduler/tests/Unit/RunScheduleCommandTest.php`

## Requirements (Test Descriptions)
- [x] `it runs every due task and reports the result counts`
- [x] `it keeps running remaining due tasks after one fails`
- [x] `it skips an overlapping task whose mutex is held`
- [x] `it releases the mutex when the task throws`
- [x] `it throws before running anything when an overlapping task has no description`
- [x] `it exits with 1 when a task fails`
- [x] `it exits with 0 when every due task succeeds`

## Acceptance Criteria
- All requirements have passing tests
- `RunScheduleCommand` delegates to `ScheduleRunner`

## Implementation Notes
(Left blank - filled in by programmer during implementation)
