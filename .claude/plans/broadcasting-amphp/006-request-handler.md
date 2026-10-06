# Task 006: AmphpSseServer start, StreamRequestHandler core and integration harness

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Build the server construction (`AmphpSseServer::start()`, heartbeat timer, PSR-3 bridge) and the core `StreamRequestHandler` (stream path, health, CORS, disconnect cleanup), plus the in-process integration harness on a random port with `tests/Support/InMemoryPubSub`. Auth, limits and replay are task 010; shutdown, periodic log and the command are task 007.

## Requirements (Test Descriptions)
- [x] `it delivers a published event to two connected clients`
- [x] `it sends a heartbeat comment`
- [x] `it keeps an idle stream open longer than the HTTP driver stream timeout` (short heartbeat, driver timeout set just above it)
- [x] `it frees the slot and subscription when the last client disconnects`
- [x] `it sends text/event-stream, no-cache and X-Accel-Buffering: no headers`
- [x] `it returns 400 when channels is missing or empty`
- [x] `it returns 404 for unknown paths`
- [x] `it sends CORS headers for allowed origins and omits them for others`
- [x] `it reports connection counts on the health endpoint`
- [x] `it forwards http-server log records to the Marko logger` (PSR-3 bridge unit test)

## Acceptance Criteria
- All requirements have passing tests
- No test leaves a server, timer or subscription alive (stop in `afterEach`), so `--parallel` runs stay stable

## Implementation Notes
- Use the plain `SocketHttpServer` constructor (no compression, no concurrency limit) with `new DefaultHttpDriverFactory($logger, streamTimeout: max(60, heartbeat * 3))`; see `_plan.md` Shared contracts for why. Make the driver timeout overridable on `AmphpSseServer` so the idle-stream test can run in a few seconds.
- `start()` must support port 0 and expose the bound port (`$server->getServers()[0]->getAddress()->getPort()`) for tests.
- Detect disconnects with `$request->getClient()->onClose()` and leave the hub there; heartbeat timers must be cancelled on disconnect.
- PSR-3 bridge: small `Log\PsrLoggerBridge implements Psr\Log\LoggerInterface` forwarding to `Marko\Log\Contracts\LoggerInterface`. Never log request query strings (they carry tokens).
- Tests use `Amp\Socket\connect()` with raw HTTP/1.1 requests and explicit read timeouts; do not add amphp/http-client.
- Leave clear extension points in the handler (a pre-stream check step and a "before live frames" step) for task 010.
