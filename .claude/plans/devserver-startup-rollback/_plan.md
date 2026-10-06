# Plan: Devserver Startup Rollback and IPv6 Hosts

## Created
2026-10-06

## Status
completed

## Objective
Make `marko up` all-or-nothing: a failure while starting any service stops every service already started (foreground and detached), and make IPv6 hosts (`::1`, `[::1]`, `::`) work with `php -S`, `isPortAvailable()`, `waitUntilAccepting()` and `dev:open`.

## Related Issues
Closes #296

## Discovery Notes
- `DevUpCommand::execute()` starts Docker, frontend, pub/sub, `dev.processes` and the PHP server in sequence with no rollback. The PID file is written only at the end.
- `ProcessManager::startDetached()` records only the PID, not a `proc_open` resource, so `stop()`/`stopAll()` silently skip detached processes. Rollback in detached mode therefore needs `ProcessManager` to track and stop detached process groups too (SIGTERM, wait, SIGKILL, wait — the same escalation `terminate()` already uses).
- A failed `startDetached()` sends SIGTERM to the group without waiting, so children of a command that exited non-zero could survive.
- `hostForUri()` is private in `ProcessManager`; `DevOpenCommand` has no access to it and always opens `localhost`. The PID file does not record the host.
- `proc_get_status()` caches the exit code since PHP 8.3, so the exit code can be read after `waitUntilAccepting()` saw the exit.
- Tests already have `devserverWaitUntil()` and marker-file helpers; `devserverListen()`/`devserverFreePort()`/`devserverStopDetached()` live in `ProcessManagerTest.php` and move to `tests/Helpers.php` so `DevUpCommandTest` can use them.

## Scope

### In Scope
- `ServerHost` value object: validates and normalizes `--host`/`dev.host` (hostname, IPv4, bare or bracketed IPv6), formats it for URIs and browsers
- `ProcessManager` tracks detached processes; `stop()`/`stopAll()` stop them with SIGKILL escalation; a failed `startDetached()` leaves nothing behind
- `ProcessManager::getExitCode()` and `collectOutput()` public for the foreground "server exited" report
- `DevServerException::invalidHost()`, `serverExited()`, `rollbackFailed()`
- `DevUpCommand` rollback on any failure, `serverExited` vs `portInUse` decision, IPv6 `php -S [::1]:PORT`
- PID file records the PHP server host; `dev:open` opens the configured host (wildcards become `localhost`)
- Docs page updates

### Out of Scope
- Writing the PID file incrementally (rollback is the default the issue chose)
- Changing the `-t public/` working-directory assumption

## Success Criteria
- [x] Every exit criterion in #296 met
- [x] All tests passing, real-process tests poll instead of sleeping
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | ServerHost value object | - (co-dependent with 002) | completed |
| 002 | New DevServerException factories | - (co-dependent with 001) | completed |
| 003 | ProcessManager stops detached processes, exposes exit code and output | 001 | completed |
| 004 | DevUpCommand IPv6 host handling | 001, 002, 003 | completed |
| 005 | DevUpCommand rollback and serverExited | 002, 003, 004 | completed |
| 006 | dev:open uses the recorded host | 001, 005 | completed |
| 007 | Docs page | 001-006 | completed |

## Architecture Notes
- `ServerHost` is a `readonly class` in `Marko\DevServer\Process` with a static `fromString()` factory that throws `DevServerException::invalidHost()`.
- 001 and 002 reference each other (`fromString()` throws `invalidHost()`, and `serverExited()` uses `ServerHost::formatForUri()`), so they land together. Tasks 003-006 are sequential because they share `ProcessManager.php`, `DevUpCommand.php` and the test helpers.
- The rollback `try` begins after the guards and the port pre-check, so a failure can never clear the PID file of a running environment. `pidFile->write()` is inside the `try`; `runForeground()` is outside it.
- Rollback catches `Throwable` (not only `DevServerException`) so a config error mid-sequence cannot orphan services either; the original is always rethrown, or wrapped as `previous` when the rollback itself fails.

## Risks & Mitigations
- IPv6 loopback missing on CI: the IPv6 tests skip when `[::1]` cannot be bound.
- Tests that start a real PHP server depend on cwd for `-t public/`: the test chdirs to its temp project and restores cwd in `finally`.
