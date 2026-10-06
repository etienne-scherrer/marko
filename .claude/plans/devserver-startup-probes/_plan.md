# Plan: Devserver Startup Probes

## Created
2026-10-05

## Status
completed

## Objective
Remove the remaining fixed sleeps from `marko/devserver` (tests and production) so that "command not found", "not executable" and "port in use" are reported loudly and deterministically by `dev:up` in both foreground and detached modes.

## Related Issues
Closes #256

## Discovery Notes
- #248 (closes #219) added `devserverWaitUntil()` (`packages/devserver/tests/Helpers.php`) and made `ProcessManager::start()` poll a probe window (default 0.15s) for a 126/127 exit.
- Remaining fixed sleeps: `DevDownCommandTest` (200ms fork wait, 100ms signal wait, hand-rolled 10 x 50ms loop), `PidFileTest` (200ms parent-exit wait), `ProcessManager::startDetached()` (one 150ms sleep + one check), `DevUpCommand` foreground (100ms sleep + one `isRunning('php')` check).
- `startDetached()` launches `tail -f /dev/null | <wrapped> > /dev/null 2>&1 & echo $!`. Verified locally that the `tail` keep-alive is orphaned and outlives the service forever (it never writes, so never gets SIGPIPE, and it is not in the service's process group, so `dev:down` never kills it). The detached process is reparented to init, so its exit code is otherwise unobservable.
- `startDetached()` has no tests today.
- `DevUpCommandTest` uses a `FakeProcessManager` subclass; new `ProcessManager` methods called by `DevUpCommand` must be overridable there so the command tests stay hermetic (no real ports).
- #221 leaves devserver timing out of scope: use `microtime(true)`/`hrtime`, not `ClockInterface`.

## Scope

### In Scope
- Executable pre-flight in `start()`/`startDetached()`: resolve the command's executable (`command -v`, or `is_file`/`is_executable` for paths) and throw `processFailedToStart` before spawning when it is missing or not executable.
- `processFailedToStart()` gains an optional reason, so the error says why (not found in PATH, not executable, exited with code N).
- `start()` default probe window raised from 0.15s to 0.5s.
- `startDetached()` polls the probe window; a small PHP supervisor (session leader) runs the command with a stdin pipe it holds open, waits for it, and writes the exit status to a status file in a temp dir. Replaces the leaking `tail -f /dev/null` keep-alive.
- `ProcessManager::isPortAvailable()` (bind probe) and `ProcessManager::waitUntilAccepting()` (connect probe until accepted / process exited / timeout).
- `DevUpCommand`: fail fast with `portInUse` before starting anything when the port cannot be bound; in foreground mode wait until the PHP server accepts connections (or exits → `portInUse`) instead of sleeping 100ms.
- Tests: no fixed sleeps used as waits in `packages/devserver/tests`.
- Docs page + README check.

### Out of Scope
- `ClockInterface` for these waits (#221).
- Stopping already-started services when foreground startup fails (pre-existing behaviour).
- `runForeground()`'s 50ms output-polling interval (a poll interval, not a wait).

## Success Criteria
- [x] No `usleep`/`sleep` used as a wait in `packages/devserver/tests` (sleeps inside child-process scripts and the polling helper excepted)
- [x] `startDetached()` reports a command that exits 127 after 300ms as `processFailedToStart`
- [x] Foreground `dev:up` detects a port in use without a fixed sleep and returns as soon as the server accepts connections
- [x] A missing executable fails with `processFailedToStart` regardless of load
- [x] Devserver suite passes 20 consecutive runs under `--parallel`
- [x] Docs page describes startup-failure detection in foreground and detached modes
- [x] All tests passing
- [x] Code follows project standards (`composer ci` green)

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Executable pre-flight, failure reasons and 0.5s probe in start() | - | completed |
| 002 | startDetached() supervisor, status file and probe polling | 001 | completed |
| 003 | Port availability (connect + bind) and readiness probes on ProcessManager | 002 | completed |
| 004 | DevUpCommand uses port probes instead of a fixed sleep | 003 | completed |
| 005 | Replace fixed sleeps in DevDownCommandTest and PidFileTest | - | completed |
| 006 | Docs page and README | 001, 002, 003, 004 | completed |

## Architecture Notes
- Tasks 001-003 all edit `ProcessManager.php`; they are chained to avoid conflicting parallel edits.
- Waits use `microtime(true)` deadlines with a 10ms poll interval (the existing `POLL_INTERVAL_MICROS`).
- The detached supervisor is `PHP_BINARY -r '<code>'` with the command and status path base64-encoded (same quoting approach as `wrapWithNewProcessGroup()`).
- The status file lives in a per-start temp dir that `startDetached()` removes after the probe; a late write into the removed dir fails silently, so nothing leaks into the temp dir.
- Shared contracts (pinned so the chained tasks agree):
  - `DevServerException::processFailedToStart(string $name, string $command, ?string $reason = null)` (001)
  - `DevServerException::serverNotReady(string $host, int $port, float $timeoutSeconds)` (003)
  - `ProcessManager::isPortAvailable(string $host, int $port): bool` (003)
  - `ProcessManager::waitUntilAccepting(string $name, string $host, int $port): bool` (003)
  - Constructor order: `Output $output, float $stopTimeoutSeconds = 3.0, float $startProbeSeconds = 0.5, ?string $statusDirectory = null, float $serverReadyTimeoutSeconds = 10.0`
- Detached mode fails on **any** exit inside the probe window (keeps today's coverage of a detached `php -S` that cannot bind). Foreground `start()` keeps failing only on 126/127.
- The port check combines a connect probe with a bind probe, because PHP sets `SO_REUSEADDR` on server sockets and a bind alone gives false "available" answers on macOS/BSD.
- Task 005 does not edit `tests/Helpers.php` (it runs in parallel with 001-004).

## Risks & Mitigations
- `command -v` semantics differ across shells (bash vs dash): only pre-check simple bare words, skip shell reserved words and anything with quotes/expansions; the runtime probe still covers the rest.
- A port held by a foreign server would also accept connections: mitigated by the bind pre-check before starting the PHP server.
- Longer probe window slows `dev:up` slightly (0.5s per detached service; foreground returns early when a process exits): acceptable for correctness; documented and configurable via the constructor.
