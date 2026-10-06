# Devil's Advocate Review: devserver-startup-probes

## Critical (Must fix before building)

1. **Task 003/004: no method signatures for the new probe API.** Task 004 overrides these methods in `FakeProcessManager` and calls them from `DevUpCommand`, but task 003 only names them. Parameter order, return type and the timeout exception factory are left open, so 003 and 004 can disagree. Fix: pinned `isPortAvailable(string $host, int $port): bool`, `waitUntilAccepting(string $name, string $host, int $port): bool`, the constructor parameter `float $serverReadyTimeoutSeconds = 10.0` (appended last), and `DevServerException::serverNotReady(string $host, int $port, float $timeoutSeconds)`.

2. **Task 004: two existing tests break and the fake has no defined knobs.** `DevUpCommandTest.php:867` and `:880` drive the foreground port check through `FakeProcessManager::$runningOverrides['php']` and `isRunning()`. Once `DevUpCommand` calls `waitUntilAccepting()` instead, those tests either fail or pass for the wrong reason. Fix: task 004 now defines the fake's public state (`$portAvailable`, `$acceptsConnections`, `$waitedFor`), says which tests to rewrite, and removes `$runningOverrides` and the `isRunning()` override if nothing else uses them.

3. **Task 002: early exits in detached mode must still fail for any exit code, not only 126/127.** Today `startDetached()` throws on any death inside the window, and the comment says this covers "port in use" (a `php -S` that fails to bind exits with code 1). The plan only describes reporting 126/127 "with its code". If a worker copies `start()`'s `in_array($exitcode, [126, 127])` check, a detached `php -S` on a busy port (or any service that crashes at boot) would be reported as started. Fix: added a requirement and a note that any status written inside the probe window becomes `processFailedToStart`, with the reason `exited with code N`.

## Important (Should fix before building)

4. **Task 003: the bind probe gives wrong answers on macOS/BSD.** PHP's `stream_socket_server()` always sets `SO_REUSEADDR`. On BSD/macOS, that lets a bind to `127.0.0.1:P` succeed while another process listens on `0.0.0.0:P`, and the reverse is also true. A bind probe alone would call an occupied port "available". Separately, a bind can fail for reasons other than "in use" (for example `EADDRNOTAVAIL` for a host that is not local), and reporting that as `portInUse` is a misleading error. Fix: `isPortAvailable()` returns false when a loopback-mapped TCP connect succeeds, or when the bind fails with "Address already in use". Any other bind failure returns true, and `php -S` then reports the problem loudly. Added test descriptions for both cases.

5. **Task 003: each connect attempt needs a short timeout.** Without one, `stream_socket_client()` falls back to `default_socket_timeout` (60s) and can hang on a filtered address, which overshoots `serverReadyTimeoutSeconds`. Fix: noted a per-attempt timeout capped by the remaining deadline.

6. **Task 002: there is no way for tests to stop detached processes or to see the status dir.** `ProcessManager::stop()` and `stopAll()` only act on `$this->processes` (proc_open handles). Detached PIDs live only in `$this->pids`, so `stop($name)` silently does nothing for them. Detached tests that do not kill the group themselves will leak `sleep` processes on every run, and with 20 parallel runs that adds up. A shared global temp prefix also makes "no status file left behind" impossible to assert reliably under `--parallel`. Fix: tests must kill the group with `posix_kill(-$pid, SIGTERM)` and wait using `devserverWaitUntil(!devserverProcessGroupAlive)`. Added a constructor option `?string $statusDirectory = null` (default `sys_get_temp_dir()`) so each test can use its own directory.

7. **Task 002: the plan never says what happens without ext-posix.** The devserver `composer.json` does not require ext-posix or ext-pcntl, and `wrapWithNewProcessGroup()` has a non-posix fallback. A supervisor that calls `posix_setsid()` unconditionally would fatal inside a detached child whose output goes to `/dev/null`, which is a silent failure. Fix: check up front and throw a loud `DevServerException` when posix is unavailable. Without posix, `isDetachedRunning()` already returns false, so detached mode cannot work there anyway.

8. **Task 005: a zombie still counts as a group member.** The parent process started by `proc_open` stays a zombie after `dev:down` kills it until the test reaps it. On Linux, `posix_kill(-$pgid, 0)` returns true for a group whose only member is a zombie (the existing comment at `DevDownCommandTest.php:319` warns about this). Fix: the polling condition must reap first, either with `proc_get_status($proc)` inside the condition or by calling `proc_close()` before polling.

9. **Task 005 vs 002/003/004: risk of conflicting edits to `Helpers.php`.** Task 005 runs in parallel with the 001-004 chain and lists `Helpers.php`, which the chain may also extend (for example with a free-port helper). Fix: task 005 must not modify `Helpers.php`; the existing helpers cover what it needs.

10. **Task 001: the reason argument and the stale comments are unspecified.** `PackageStructureTest.php:36` calls `processFailedToStart()` with two arguments, so the new reason must be an optional third parameter. The pre-flight must also cover the `env PHP_CLI_SERVER_WORKERS=4 php -S ...` form that `DevUpCommand` actually sends (assignments after `env`, `env` options such as `-i`/`-u`, and `~` expansion). Fix: pinned `processFailedToStart(string $name, string $command, ?string $reason = null)`, listed the shapes the parser handles and the ones it leaves to the runtime probe, and noted the stale "150ms" comments at `ProcessManager.php:30` and `ProcessManagerTest.php:87`.

## Minor (Nice to address)

- Task 001: `sleep 0.3; exit 127` against a 0.5s window leaves about 200ms for proc_open, the PHP wrapper's startup and `sh -c`. That margin is thin under `--parallel` plus coverage (xdebug startup). Watch it during the 20-run soak; 0.25s would add margin.
- Task 004: each connect probe makes `php -S` log `Accepted` and `Closing` lines, which `runForeground()` then prints as `[php] ...`. This is cosmetic, but worth a line in the docs.
- Task 002: each detached service now keeps a PHP process alive as its supervisor (previously a `tail`), costing a few MB of RSS each. Redirect the supervisor's stdin from `/dev/null` explicitly (`< /dev/null`).
- Task 002: an `unlink`+`rmdir` cleanup can lose a race with a late supervisor write and leave an empty dir behind. Either accept this or retry `rmdir` once.
- `DevUpCommand` passes IPv6 hosts to `php -S` without brackets (`php -S ::1:8000`). This bug predates the plan and is out of scope.

## Questions for the Team

- Should a detached-mode `php -S` early exit stay `processFailedToStart`, or become `portInUse` to match foreground mode? The bind pre-check now catches the common case, so this only matters for races.
- The 0.5s detached probe adds about 0.5s per detached service to `dev:up` (around 2s with docker + frontend + pubsub + php). Is that acceptable, or should the window be configurable through `config/dev.php` instead of only through the constructor?
