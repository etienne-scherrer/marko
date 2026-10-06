# Task 001: Bounded, escalating ProcessManager::stop()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Make `stop()` send SIGTERM to the process and its process group, poll until both are gone up to a configurable deadline, then escalate to SIGKILL, so that "stopped" deterministically means "gone".

## Context
- Related files: packages/devserver/src/Process/ProcessManager.php, packages/devserver/tests/Process/ProcessManagerTest.php, packages/devserver/tests/Helpers.php (new), packages/devserver/tests/HelpersTest.php (new), composer.json (root)
- Patterns to follow: `PidFile::isProcessGroupRunning()` for group liveness checks

## Requirements (Test Descriptions)
- [ ] `it returns true as soon as the waited-for condition holds`
- [ ] `it returns false when the waited-for condition never holds before the deadline`
- [ ] `it leaves no process in the group once stop returns`
- [ ] `it escalates to SIGKILL when a process ignores SIGTERM`
- [ ] `it returns within the stop timeout when a process ignores SIGTERM`
- [ ] `it stops child processes that share the process group`
- [ ] `it stops a process that has already exited without waiting`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
- Built as implemented: `stopTimeoutSeconds` (default 3.0) constructor parameter. `stop()` and `stopAll()` share a private `terminate(list $names)`: skip already-gone processes, SIGTERM leader + group, poll every 10 ms (shared deadline across all names), SIGKILL survivors, wait up to 2 s more, then close pipes and `proc_close()`. A group that survives SIGKILL throws `DevServerException::processFailedToStop()`.
- Pipes are closed after the group is gone (not before signalling), so a service writing during its grace period does not get SIGPIPE.
- `stopAll()` overlaps grace periods (review item "Ctrl+C can be slow"); covered by `it overlaps the stop grace periods of all processes in stopAll`.
- `proc_terminate()` is only called while `proc_get_status()` reports the leader running, so a reaped (possibly reused) PID is never signalled.
- Helpers live in `packages/devserver/tests/Helpers.php` (`devserverWaitUntil`, `devserverProcessGroupAlive`, `devserverMarkerPath`) with tests in `packages/devserver/tests/HelpersTest.php`.
- **Polling helper (moved here from 002):** `devserverWaitUntil(callable $condition, float $timeoutSeconds = 5.0, int $intervalMicros = 10_000): bool` goes in `packages/devserver/tests/Helpers.php`, wrapped in `if (!function_exists(...))`. Register it in root `composer.json` `autoload-dev.files` (alphabetical, after `packages/devai/tests/Helpers.php`) and run `composer dump-autoload`. Do NOT use `tests/Pest.php`: package Pest.php files are not loaded when the suite runs from the root.
- **Readiness before stop():** tests for SIGTERM-ignoring processes and for group children must not rely on `start()`'s 150 ms sleep. The command writes a marker file once it is ready (e.g. `trap '' TERM; echo ready > $marker; sleep 30`, or `sleep 30 & echo $! > $marker; wait`). The test polls with `devserverWaitUntil` for the marker, then calls `stop()`. Use per-test temp files (`sys_get_temp_dir()` + `uniqid()`) and clean them up.
- Use signal literals `15`/`9` (as in `PidFile::killProcessGroup()`). `SIGTERM`/`SIGKILL` constants are only defined when pcntl is loaded. Without `posix_kill`, escalate with `proc_terminate($process, 9)`.
- Leader liveness comes from `proc_get_status()`, never `posix_kill($pid, 0)`, because zombies answer signal 0. Group checks use `@posix_kill(-$pid, 0)` with a `ValueError` catch and a `$pid > 0` guard.
- `stop()` must return early for unknown names before it reads `stopTimeoutSeconds`. `DevUpCommandTest::FakeProcessManager` skips the parent constructor, so the property is uninitialized there.
- SIGKILL tests construct with a short timeout (e.g. `stopTimeoutSeconds: 0.3`). Duration assertions need generous slack (≤ timeout + 2 s) so they stay stable under parallel load.
