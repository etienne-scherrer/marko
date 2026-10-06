# Task 004: DevUpCommand uses port probes instead of a fixed sleep

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
`DevUpCommand` checks the port before starting any service and throws `portInUse` if it cannot be bound. In foreground mode it then waits until the PHP server accepts connections (or throws `portInUse` if the server exits first), replacing `usleep(100000)` + `isRunning('php')`.

## Context
- Related files: `packages/devserver/src/Command/DevUpCommand.php`, `packages/devserver/tests/Command/DevUpCommandTest.php` (`FakeProcessManager` must override the new methods so command tests never touch real ports)
- Builds against task 003's contract: `isPortAvailable(string $host, int $port): bool` and `waitUntilAccepting(string $name, string $host, int $port): bool` (throws `serverNotReady` on timeout).
- The port check runs in **both** modes, after the existing guards (already running, missing `public/index.php`) and before the first `$startProcess(...)` call (docker).
- Foreground: `if (!$this->processManager->waitUntilAccepting('php', $host, $port)) { throw DevServerException::portInUse($port); }`. This replaces `usleep(100000)` and `isRunning('php')`. Update the comment at `DevUpCommand.php:176-177`.
- `FakeProcessManager` changes:
  - add `public bool $portAvailable = true;`, `public bool $acceptsConnections = true;`, and `public array $waitedFor = [];` (records `[name, host, port]`)
  - override both new methods
  - remove `$runningOverrides` and the `isRunning()` override if nothing else uses them
- Existing tests that must be rewritten (they drive the old `isRunning('php')` path and would otherwise pass vacuously or fail):
  - `it throws DevServerException when PHP server dies immediately after start in foreground mode` (line ~867): use `$acceptsConnections = false`
  - `it does not throw when PHP server stays running after start in foreground mode` (line ~880)
- Real-ProcessManager test: hold a listener on `tcp://127.0.0.1:0`, pass its port through `--port` with `--host=127.0.0.1`, and assert `portInUse` is thrown. Because the check runs first, no real process is spawned. Close the listener in `finally`.

## Requirements (Test Descriptions)
- [x] `it throws portInUse before starting any process when the port is occupied`
- [x] `it throws portInUse for an occupied port with the real process manager`
- [x] `it throws DevServerException when PHP server exits before accepting connections in foreground mode`
- [x] `it waits for the PHP server to accept connections in foreground mode`
- [x] `it does not wait for connections in detached mode`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
