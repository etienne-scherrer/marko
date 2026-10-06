# Task 005: DevUpCommand rollback and serverExited

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Wrap the start sequence in `try`/`catch`; on failure stop every started service, clear a stale PID file in detached mode and rethrow the original error, or throw `rollbackFailed()` naming the survivors. In foreground mode, report a PHP server that exits before accepting connections with `serverExited()` unless the port really is taken.

## Context
- Related files: packages/devserver/src/Command/DevUpCommand.php, packages/devserver/tests/Command/DevUpCommandTest.php
- Existing contracts: `DevServerException::serverExited(string $host, int $port, ?int $exitCode, string $output)`, `DevServerException::rollbackFailed(Throwable $original, array<string,int> $survivors)`, and `ProcessManager::getExitCode(): ?int`/`collectOutput(): string` (task 003).

## Implementation constraints
- **Try scope:**
  - The `try` starts after the "already running" guard, the `public/index.php` check and the `isPortAvailable()` pre-check. Nothing has started before that point, and the catch must never clear the PID file of an environment that is still running.
  - `pidFile->write()` is inside the `try`, because a write failure would otherwise orphan every service.
  - `runForeground()` is outside the `try`.
- **Catch (`Throwable $e`):**
  1. Call `processManager->stopAll()`.
  2. If it throws, throw `rollbackFailed($e, $this->processManager->getPids())`. The survivors stay in `getPids()`, and the original error (not the stop error) is `previous`.
  3. In detached mode, `pidFile->clear()`.
  4. Rethrow `$e`.
- **serverExited ordering:** when `waitUntilAccepting()` returns false, read `getExitCode('php')` and `collectOutput('php')` before any stop or rollback, because `stop()` closes the pipes. Then decide: if `isPortAvailable($address, $port)` is now false, throw `portInUse`; otherwise throw `serverExited`.
- **FakeProcessManager** (skips the parent constructor) needs:
  - a queue or separate flag for the post-exit `isPortAvailable()` answer. The pre-check and the post-exit check must be controllable independently. Update the existing `acceptsConnections = false` test (~line 893) accordingly.
  - overridable `getExitCode()` and `collectOutput()` values
  - a `stopAll()` that records calls and can throw `processFailedToStop`
  - a `getPids()` that returns configured survivors
- Real-process rollback tests use the real `ProcessManager` and assert that every started PID/group is gone, by polling with `devserverWaitUntil()`.

## Requirements (Test Descriptions)
- [x] `it stops services already started when a later service fails to start` (foreground and detached, real processes)
- [x] `it stops services already started when the PHP server fails to start` (foreground and detached, real processes)
- [x] `it leaves no PID file behind after a failed detached start`
- [x] `it throws a DevServerException naming the survivors when the rollback fails`
- [x] `it reports a PHP server that exits before accepting connections with its exit code and output`
- [x] `it reports portInUse when the PHP server exits because the port was taken`
- [x] `it keeps the PID file of a running environment when the already-running guard fails`
- [x] `it rolls back when a non-DevServerException is thrown mid-sequence` (e.g. a config error)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
