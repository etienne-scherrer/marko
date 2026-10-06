# Task 004: scheduler: replace Closure clocks with ClockInterface

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`FileTaskMutex`, `ScheduleWorkCommand` and `RunScheduleCommand` take a required `ClockInterface`. The undocumented `Closure` clock parameters are removed.

## Context
- Related files: packages/scheduler/src/Mutex/FileTaskMutex.php, src/Command/ScheduleWorkCommand.php, src/Command/RunScheduleCommand.php, module.php, composer.json (add marko/clock, dev marko/testing)

## Requirements (Test Descriptions)
- [ ] `it runs the tasks due at the injected clock time`
- [ ] `it writes the mutex expiry from the injected clock`
- [ ] `it treats an expired mutex as stale once the clock passes its expiry`
- [ ] `it waits until the top of the next minute by the injected clock`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Check current source first: this worktree's `ScheduleWorkCommand` and its test may already be partly migrated.
- `module.php` builds `FileTaskMutex` in a closure (`new FileTaskMutex(directory: ...)`), so it will NOT autowire. Add `clock: $container->get(ClockInterface::class)`.
- `FileTaskMutex` works in Unix ints: use `$this->clock->now()->getTimestamp()` in place of `time()`.
- `ScheduleWorkCommand` keeps `?Closure $sleeper = null` after the required clock. Its loop re-reads the clock until the next minute arrives, so the test sleeper MUST `travel("+$seconds seconds")` on the FakeClock, or the test loops forever.
- Update all `new FileTaskMutex(` / `new ScheduleWorkCommand(` / `new RunScheduleCommand(` sites in `scheduler/tests` (FileTaskMutexTest, ScheduleRunnerTest, ScheduleWorkCommandTest, RunScheduleCommandTest).
