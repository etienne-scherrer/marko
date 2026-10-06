# Task 001: Executable pre-flight, failure reasons and 0.5s probe in start()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Detect "command not found"/"not executable" before spawning, so the error no longer depends on how fast `sh -c` reports 127/126 under load. Give `processFailedToStart()` an optional reason and raise `start()`'s default probe window to 0.5s.

## Context
- Related files: `packages/devserver/src/Process/ProcessManager.php`, `packages/devserver/src/Exceptions/DevServerException.php`, `packages/devserver/tests/Process/ProcessManagerTest.php`
- Resolve the first word of the command (skipping leading `VAR=value` assignments and `exec`/`env` prefixes). Bare names go through `command -v`; names containing `/` must be an executable regular file. Anything that cannot be resolved statically (quotes, `$`, globs, reserved words, subshells) is left to the runtime probe.
- Must handle the exact shape `DevUpCommand` sends: `env PHP_CLI_SERVER_WORKERS=4 php -S host:port -t public/`. After `env`, skip any further `VAR=value` words. If `env` is followed by an option (`-i`, `-u name`, `-S`, ...), or a word starts with `~`, leave it to the runtime probe.
- Contract (other tasks depend on it): `DevServerException::processFailedToStart(string $name, string $command, ?string $reason = null)`. The reason is optional so the existing two-argument call in `tests/PackageStructureTest.php:36` keeps working. Reason strings: `not found in PATH`, `not executable`, `exited with code N`.
- Constructor default `startProbeSeconds` goes from 0.15 to 0.5. Update the stale "150ms" docblock at `ProcessManager.php:30` and the comment at `ProcessManagerTest.php:87`.
- Do NOT use `ClockInterface` (#221). Keep `microtime(true)` deadlines.

## Requirements (Test Descriptions)
- [x] `it fails with processFailedToStart for a missing executable without relying on the probe window`
- [x] `it fails with processFailedToStart for a path that is not executable`
- [x] `it resolves the executable after leading environment assignments and env`
- [x] `it leaves shell syntax it cannot resolve to the runtime probe`
- [x] `it names the reason a process failed to start in the exception`
- [x] `it detects a command that exits 127 after 300ms with the default probe window`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- The 300ms-vs-0.5s-default test was replaced, per the devil's-advocate flakiness note, by two deterministic tests: `it reports the exit code of a command that fails within the probe window` (explicit 5s window, returns early on exit) and `it watches a running process for half a second by default` (asserts the 0.5s default as a lower bound).
- The reason is appended to the exception message, e.g. `... with command: npx vite (Executable 'npx' was not found in PATH)`.
- Unrelated ProcessManager tests pin `startProbeSeconds: 0.15` (the old default) to keep suite time down.
