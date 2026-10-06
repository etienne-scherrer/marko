# Task 002: startDetached() supervisor, status file and probe polling

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Replace the single 150ms sleep in `startDetached()` with polling for the probe window. Run the command under a small PHP supervisor that becomes the session leader, holds the command's stdin open (replacing the orphaned `tail -f /dev/null`), waits for it and writes its exit status to a status file, so an early 126/127 exit is reported with its code.

## Context
- Related files: `packages/devserver/src/Process/ProcessManager.php`, `packages/devserver/tests/Process/ProcessManagerTest.php`
- The returned PID must be the process-group leader (dev:status and dev:down signal `-pid`).
- The status file lives in a per-start temp dir removed after the probe; late writes fail silently.
- **Any exit inside the probe window fails, not just 126/127.** Today `startDetached()` throws on any early death, which is how a detached `php -S` on a busy port (exit 1) gets caught. Do not copy `start()`'s 126/127 filter here. A status written inside the window means `processFailedToStart($name, $command, "exited with code N")`. A missing or not-executable command found by the pre-flight from task 001 throws before anything is spawned.
- A running process cannot be reported early: when nothing is written, `startDetached()` waits out the full window and then returns the PID.
- Constructor gains `?string $statusDirectory = null` (appended after `startProbeSeconds`, default `sys_get_temp_dir()`). It is the parent dir for the per-start status dirs, so tests can pass their own dir and assert it is left empty. Task 003 appends its own parameter after this one.
- ext-posix is optional in `composer.json`. When `posix_setsid`/`posix_kill` are unavailable, throw a loud `DevServerException` saying detached mode requires ext-posix. Never let the supervisor fatal silently inside a child whose output goes to `/dev/null`.
- Launch the supervisor with `< /dev/null > /dev/null 2>&1 &`. The supervisor runs the command with `proc_open(['/bin/sh', '-c', $command], ...)`, holds pipe 0 open, never writes to it, waits, and then writes the exit code with `@file_put_contents`.
- **Test cleanup:** `stop()`/`stopAll()` only act on proc_open handles (`$this->processes`), so they do nothing for detached PIDs. Every detached test must end with `posix_kill(-$pid, SIGTERM)` followed by `devserverWaitUntil(fn () => !devserverProcessGroupAlive($pid))`, inside `try/finally`, so no `sleep` processes leak across the 20-run `--parallel` soak.
- Do NOT use `ClockInterface` (#221).

## Requirements (Test Descriptions)
- [x] `it starts a detached process that keeps running after startDetached returns`
- [x] `it makes the detached PID the leader of a process group containing the command`
- [x] `it keeps stdin open for a detached process until it exits`
- [x] `it reports a detached command that exits 127 after 300ms as processFailedToStart`
- [x] `it reports a missing detached executable as processFailedToStart`
- [x] `it reports a detached command that exits with a non-127 code inside the probe window as processFailedToStart`- [ ] `it leaves no keep-alive or status file behind once a detached process is stopped`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
