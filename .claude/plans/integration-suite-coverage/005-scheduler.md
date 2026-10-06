# Task 005: Scheduler — schedule:run and overlap (#164)

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
Convert the #164 todos into `tests/Integration/App/SchedulerTest.php`. Register an overlap-protected task in the fixture boot callback and drop the stale "finds nothing (#164)" comment.

## Context
- Related files: `Fixture/app/integration/module.php`, `packages/scheduler/src/ScheduleRunner.php`, `packages/scheduler/src/Mutex/FileTaskMutex.php`

## Requirements (Test Descriptions)
- [x] `it runs the task registered in the fixture boot callback with schedule:run`
- [x] `it skips a scheduled task whose previous run is still in progress`

## Acceptance Criteria
- Tests pass

## Implementation Notes
- `ScheduleRunner` validates every `withoutOverlapping()` task up front. A task with no `description()` makes **every** `schedule:run` throw, which also breaks the heartbeat test, so give the new task a description.
- Both fixture tasks run every minute. Assert the output with `toContain`, not an exact match.
- Hold the lock through the container's `TaskMutexInterface` singleton: `FileTaskMutex::acquire()` returns false while it holds the handle. A second `new FileTaskMutex("{project}/storage/framework")` also works (separate flock fd). Release it afterwards.
