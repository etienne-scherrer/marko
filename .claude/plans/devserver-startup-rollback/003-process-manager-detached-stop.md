# Task 003: ProcessManager stops detached processes and exposes exit code and output

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Track detached process groups so `stop()`/`stopAll()` stop them with the same SIGTERM → SIGKILL escalation, make a failed `startDetached()` leave nothing behind, and expose `getExitCode()` and `collectOutput()` for the foreground "server exited" report.

## Context
- Related files: packages/devserver/src/Process/ProcessManager.php, packages/devserver/tests/Process/ProcessManagerTest.php, packages/devserver/tests/Helpers.php
- Tests use real short-lived processes and poll; no fixed sleeps.
- 001 already replaced `hostForUri()` with `ServerHost::formatForUri()` in this file; build on top of it.
- Move `devserverListen()`, `devserverFreePort()` and `devserverStopDetached()` from `ProcessManagerTest.php` to `tests/Helpers.php` (task 004 needs them).

## Interface contract (consumed by 005)
- `public function getExitCode(string $name): ?int` returns the exit code of an exited attached process, or null while it runs or when the name is unknown. Update `runForeground()` to work with the nullable return.
- `public function collectOutput(string $name): string` returns the unread stdout+stderr of an attached process without writing it to Output. It returns `''` for an unknown name. It must be callable before `stop()`, because `stop()` closes the pipes.
- `getPids()` keeps returning attached + detached PIDs. After `stop()`/`stopAll()` throws `processFailedToStop`, every survivor (attached or detached) stays in `getPids()`, and every stopped process is removed.

## Implementation constraints
- Track detached PIDs in a separate map (e.g. `private array $detached` name => pid). `terminate()`, `signalAll()` and `waitForExit()` currently dereference `$this->processes[$name]['resource']` unconditionally, so they must branch for detached names:
  - signal both `-pid` and `pid` via `posix_kill`, because the supervisor may not have called `setsid()` yet
  - use `isDetachedRunning()` for liveness
  - there are no pipes or `proc_close` to handle
- `stop()` must handle detached names, not return early. `stopAll()` must not return early when only detached processes are tracked. It stops attached and detached processes in one shared grace period.
- A failed `startDetached()` stops the group with the same SIGTERM → wait → SIGKILL → wait escalation. It never adds the name to the tracked maps.
- Existing tests that call `devserverStopDetached()` after `stopAll()` must keep passing (stopping an already-gone group is a no-op).

## Requirements (Test Descriptions)
- [x] `it stops a detached process group with stop`
- [x] `it stops detached and attached processes together with stopAll`
- [x] `it escalates to SIGKILL for a detached process group that ignores SIGTERM`
- [x] `it leaves no process from a detached command that fails during the probe window`
- [x] `it reports the exit code of an exited process and null while it runs`
- [x] `it collects the unread output of a process`
- [x] `it stops detached processes with stopAll when no attached process is tracked`
- [x] `it keeps every survivor in getPids when a stop fails`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
