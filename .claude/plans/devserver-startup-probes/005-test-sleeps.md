# Task 005: Replace fixed sleeps in DevDownCommandTest and PidFileTest

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace each fixed sleep used as a wait with `devserverWaitUntil()` on the real condition: the fork completed (child writes a marker file), the parent exited (`proc_get_status()['running'] === false`), and signals propagated (process group gone).

## Context
- Related files: `packages/devserver/tests/Command/DevDownCommandTest.php`, `packages/devserver/tests/Process/PidFileTest.php`
- Use the existing helpers in `packages/devserver/tests/Helpers.php` (`devserverWaitUntil`, `devserverProcessGroupAlive`, `devserverMarkerPath`). **Do not modify `Helpers.php`**: this task runs in parallel with the 001-004 chain, which may add helpers there.
- Use array-form `proc_open` so the reported PID is PHP's, not a wrapper shell's (dash does not exec in place).
- **Zombie gotcha (DevDownCommandTest):** the forked parent is the test process's proc_open child. After `dev:down` kills it, it stays a zombie until reaped, and on Linux `posix_kill(-$pgid, 0)` still returns true for that group. The group-gone wait must reap first: call `proc_get_status($proc)` inside the `devserverWaitUntil` condition, or `proc_close($proc)` before polling. Keep the existing comment about this.
- Assert on the `devserverWaitUntil(...)` return value (`->toBeTrue()`). Do not ignore it.
- Unlink marker files when done.

## Requirements (Test Descriptions)
- [x] `it kills entire process group when stopping a process` (waits for the child's marker instead of 200ms, polls the group instead of 100ms + hand-rolled loop)
- [x] `it detects a process group as running when parent died but child lives` (waits for the parent to exit instead of 200ms)
- [x] `it leaves no sleep used as a wait in the devserver tests` (verified by grep in the PR, not a test)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
