# Task 005: schedule:work Command

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
Add `#[Command(name: 'schedule:work')]` that runs the `ScheduleRunner` at the top of every minute in the foreground until SIGINT/SIGTERM. Clock and sleeper are injectable closures so tests never really sleep.

## Context
- Related files: `packages/scheduler/src/Command/`, `packages/devserver/src/Process/ProcessManager.php` (signal handling precedent)

## Requirements (Test Descriptions)
- [x] `it registers the schedule:work command`
- [x] `it sleeps until the next minute boundary before running`
- [x] `it runs due tasks once per minute boundary`
- [x] `it only runs tasks that are due at each minute boundary`
- [x] `it waits for the following boundary when a run overruns the minute`
- [x] `it keeps working after a task fails`
- [x] `it stops when stop is called`

## Acceptance Criteria
- All requirements have passing tests
- `pcntl_async_signals` used only when available; handlers restored on exit

## Implementation Notes
Cron matching is minute-granular, so "evaluates at the start of the minute" was replaced by the due-filtering and overrun tests, which pin observable behavior. The runner is passed the boundary time (`HH:MM:00`), not the post-sleep clock. Clock is `Closure(): DateTimeImmutable`, sleeper `Closure(int): mixed`; both default to real time/`sleep()` and stay autowirable.
