# Task 003: Port availability and readiness probes on ProcessManager

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Add `isPortAvailable(host, port)` (try to bind the address) and `waitUntilAccepting(name, host, port)` (poll a TCP connect until the server accepts, the process exits, or a bounded timeout passes) so `dev:up` can use the real readiness signal instead of a fixed sleep.

## Context
- Related files: `packages/devserver/src/Process/ProcessManager.php`, `packages/devserver/src/Exceptions/DevServerException.php`, `packages/devserver/tests/Process/ProcessManagerTest.php`
- Wildcard hosts (`0.0.0.0`, `::`) are probed through loopback (`127.0.0.1`, `[::1]`) for the **connect** probe; the bind probe binds the host as given. IPv6 literals need brackets in stream URIs.
- Timeout configurable through the constructor (`serverReadyTimeoutSeconds`, default 10s); timing out throws a loud `DevServerException`.

### Contract (task 004 builds against this; do not deviate)
- `public function isPortAvailable(string $host, int $port): bool`
- `public function waitUntilAccepting(string $name, string $host, int $port): bool`: returns true once a TCP connect succeeds, and false once `isRunning($name)` is false (the process exited first). Throws `DevServerException::serverNotReady($host, $port, $timeoutSeconds)` when the timeout passes.
- New factory `DevServerException::serverNotReady(string $host, int $port, float $timeoutSeconds): self`
- Constructor: append `private readonly float $serverReadyTimeoutSeconds = 10.0` as the last parameter, after the parameters from tasks 001/002, so existing positional/named calls keep working.

### Gotchas
- PHP's `stream_socket_server()` always sets `SO_REUSEADDR`. On macOS/BSD that lets a bind to `127.0.0.1:P` succeed while another process listens on `0.0.0.0:P`, and the reverse is also true. A bind probe alone gives false "available" answers. `isPortAvailable()` must return **false** if a loopback-mapped connect to host:port succeeds, **or** if the bind fails with "Address already in use" (check `$errstr`/`$errno`, using `SOCKET_EADDRINUSE` only when ext-sockets is loaded). Any other bind failure, such as `EADDRNOTAVAIL` for a host that is not local, returns **true**. A misleading "port in use" is worse than letting `php -S` fail loudly.
- Close the probe socket (`fclose`) before returning so `php -S` can bind.
- Each connect attempt needs a short timeout (for example `min(0.1, remaining)`). Never rely on `default_socket_timeout` (60s), or one attempt can overshoot the deadline.
- Use `microtime(true)` deadlines and the existing `POLL_INTERVAL_MICROS`. Do NOT use `ClockInterface` (#221).
- Tests get a free port by binding `tcp://127.0.0.1:0` and reading it with `stream_socket_get_name()`. Never hardcode ports, because the suite runs `--parallel`.

## Requirements (Test Descriptions)
- [x] `it reports a port another process is listening on as unavailable`
- [x] `it reports a port held on the wildcard address as unavailable when probing loopback`
- [x] `it does not report an unbindable non-local host as a port in use`
- [x] `it reports a free port as available`
- [x] `it returns true as soon as the server accepts connections`
- [x] `it returns false when the process exits before accepting connections`
- [x] `it throws when the server neither accepts connections nor exits before the timeout`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
