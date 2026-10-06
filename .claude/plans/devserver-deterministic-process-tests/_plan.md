# Plan: Devserver Deterministic Process Tests

## Created
2026-10-05

## Status
completed

## Objective
Make `ProcessManager::stop()` guarantee the process and its process group are gone when it returns (SIGTERM, bounded wait, SIGKILL), and remove every fixed sleep from `ProcessManagerTest` so the suite stops flaking under `--parallel` load.

## Related Issues
Closes #219

## Discovery Notes
- `ProcessManager::stop()` closed pipes, called `proc_terminate()` (SIGTERM to the leader PID only) and `proc_close()`. Children in the process group created by the `posix_setsid()` wrapper were never signalled, and nothing bounded the wait if the leader ignored SIGTERM (`proc_close()` would block forever).
- `dev:down` does not use `ProcessManager::stop()`; it uses `PidFile::killProcessGroup()` for detached processes. Its visible behaviour is unchanged by this plan. `stop()` is used by foreground mode (`Ctrl+C` → `stopAll()`), by `runForeground()` cleanup and by `start()` when a command fails to launch.
- `ProcessManagerTest` uses fixed `usleep()` waits in two places (lines 69, 204, 215) and relies on timing for the "exits unexpectedly" case.
- `DevUpCommandTest::FakeProcessManager` extends `ProcessManager` and skips the parent constructor, so a new optional constructor parameter is safe.
- The container autowires scalar constructor parameters that have defaults.

## Scope

### In Scope
- Bounded, escalating `stop()` in `ProcessManager` with an optional `stopTimeoutSeconds` constructor parameter
- Unit tests for graceful stop, SIGKILL escalation and process-group cleanup
- Deadline-polling `devserverWaitUntil()` helper in `packages/devserver/tests/Helpers.php` (registered in root `composer.json` `autoload-dev.files`; package `tests/Pest.php` is not loaded from the root suite), built in 001 and used by 001's readiness waits and 002; no fixed sleeps in `ProcessManagerTest.php`
- Docs page note on stop semantics

### Out of Scope
- Changing `dev:down` / `PidFile::killProcessGroup()` (detached processes)
- Removing the fixed sleeps from other devserver test files or production `startDetached()` (`start()`'s probe became a polling window in task 004 after the stress run exposed it)

## Success Criteria
- [ ] No fixed `usleep()`/`sleep()` waits remain in `ProcessManagerTest.php`
- [ ] `stop()` guarantees the process group is gone when it returns, with unit tests
- [ ] File passes 200 consecutive runs alongside the full parallel suite
- [ ] Docs mention the stop semantics
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Polling test helper + bounded, escalating ProcessManager::stop() | - | completed |
| 002 | Replace fixed sleeps in ProcessManagerTest with polling | 001 | completed |
| 003 | Document stop semantics | 001 | completed |
| 004 | Polling start() probe (found by stress run) | 001 | completed |
| 005 | exec the setsid wrapper so the PID is the session leader (found by CI) | 001 | completed |

## Architecture Notes
- The process group id equals the leader PID because the wrapper calls `posix_setsid()` before `pcntl_exec()`.
- "Gone" means: `proc_get_status()` reports the leader not running (which also reaps it, so zombies don't count) AND `posix_kill(-$pid, 0)` fails for the group.
- Signal both the leader (`proc_terminate`) and the group (`posix_kill(-$pid, ...)`): the leader may be killed before `posix_setsid()` runs, in which case no group exists yet.

## Risks & Mitigations
- PGID reuse after the group dies: negligible within a few-second window.
- Hosts without posix/pcntl: fall back to leader-only termination (`proc_terminate($process, 9)` for escalation) with the same bounded wait. Use signal literals `15`/`9`; the `SIGTERM`/`SIGKILL` constants require pcntl.
- Tests racing `start()`'s 150ms sleep: SIGTERM-ignoring and group-child tests write a readiness marker file and poll for it before `stop()`.
