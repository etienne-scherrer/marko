# Devil's Advocate Review: devserver-startup-rollback

Note: when this review ran, `ServerHost.php` (001) and the new factories (002) were already in the worktree. The contracts below are based on that code.

## Critical (Must fix before building)

1. **001 and 002 depend on each other but the plan lists neither dependency.** `ServerHost::fromString()` throws `DevServerException::invalidHost()`, and `serverExited()` calls `ServerHost::formatForUri()`. Both have landed, so nothing is blocked now. Treat 001 and 002 as one unit that must be green before 003 starts.
2. **003, 001: both edit `ProcessManager.php` at the same time.** 001's acceptance criterion replaces `hostForUri()`, and 003 rewrites `stop`/`terminate`/`startDetached` in the same file. Fix: 003 depends on 001.
3. **004 needs test helpers that 003 moves.** 004's real-server IPv6 tests need `devserverListen()`, `devserverFreePort()` and `devserverStopDetached()`, which 003 moves into `tests/Helpers.php`. Fix: 004 depends on 003.
4. **006 edits `DevUpCommand.php`, as do 004 and 005, but 006 depends only on 001.** All three would conflict. Fix: 006 depends on 005.
5. **003: `terminate()`, `signalAll()` and `waitForExit()` dereference `$this->processes[$name]['resource']` unconditionally.** If they are reused for detached names (which have no proc resource), they crash. `stopAll()` also returns early when `$this->processes === []`, so a detached-only manager would stop nothing. Fix: track detached PIDs in their own map, and branch on it in signal and wait. Signal both `-pid` and `pid`, because the supervisor may not have called `setsid` yet. Make the `stopAll()` early return check both maps. Fix added to 003.
6. **005: a rollback that clears the PID file could delete the PID file of a running environment.** The "already running" guard throws before anything starts. If the `try` wraps the guard, the catch would stop nothing but would clear a live environment's PID file, orphaning it from `marko down`. Fix: the `try` starts after the guards and the port pre-check. Fix added to 005.
7. **005: `FakeProcessManager` in `DevUpCommandTest.php` skips the parent constructor and overrides only start, port and wait methods.**
   - The new `serverExited` path calls `getExitCode()`, `collectOutput()` and a second `isPortAvailable()`. The existing test at line ~893 (`acceptsConnections = false` → "Port 8000 is already in use") will change behaviour, because the first `isPortAvailable()` and the post-exit one read the same `$portAvailable` flag.
   - The `rollbackFailed` test needs a fake `stopAll()` that throws and a `getPids()` that returns survivors.
   - Fix: extend the fake with a queue of port answers (or a separate `portAvailableAfterExit`), plus `exitCode`, `serverOutput`, `stopAllCalls` and `stopAllThrows`/`survivors`. Fix added to 005.

## Important (Should fix before building)

8. **005: the output is lost if the manager stops `php` before reading it.** `serverExited` needs the exit code and output, but `terminate()` closes the pipes and `proc_close`s. Fix: read `getExitCode('php')` and `collectOutput('php')` before the rollback runs. Added to 005.
9. **005: the survivors of a failed rollback are not specified.** `terminate()` throws `processFailedToStop` for only the first survivor, but it leaves every survivor in `$pids`. Fix: after catching the stop failure, survivors = `getPids()`, which needs 003 to keep detached survivors in `$pids` too. The original error goes in `previous`, not the stop error. Added to 003 and 005.
10. **005: the scope of the try block is not specified.**
    - `pidFile->write()` must be inside the `try`. A write failure after every service has started would otherwise orphan them all.
    - `runForeground()` must stay outside the `try`. It already does `stopAll()` on a signal.
    - Added to 005.
11. **003: the `getExitCode()` contract changes.** It is private, returns `int`, and uses `-1` for unknown. The plan wants "null while it runs". Make it `public function getExitCode(string $name): ?int` (null for running or unknown), and keep `runForeground()` working with it. `collectOutput(string $name): string` returns the unread stdout+stderr without writing it to Output, and returns `''` for an unknown name. Added to 003.
12. **004: the existing `Invalid host value` test (DevUpCommandTest ~line 473) and the `waitedFor` assertions take the host as a string.** Spell out that `isPortAvailable()` and `waitUntilAccepting()` receive `ServerHost->address` (unbracketed). The PHP command uses `forUri()`. The "Starting PHP server" line shows the bracketed form. Added to 004.

## Minor (Nice to address)

- `serverNotReady()` formats `$host:$port` without brackets, so `::1:8000` is ambiguous. Could use `ServerHost::formatForUri()`.
- Ctrl+C during the startup sequence (before `runForeground()` registers handlers) kills `marko` but leaves children running in their own sessions. That is out of scope for #296, but it is the same orphaning class.
- 006: `DevOpenCommand` would throw `invalidHost` for a hand-edited, invalid recorded host. That is acceptable (loud), but worth a test.

## Questions for the Team

- Should a failed foreground start also clear a stale PID file left by an earlier detached run, or only in detached mode, as planned?
