# Task 007: Graceful shutdown, periodic log and serve command

**Status**: completed
**Depends on**: 010
**Retry count**: 0

## Description
`AmphpSseServer::stop()` with graceful shutdown, the periodic connection-count log, and the `broadcasting:serve` command; optional live Redis test. Server construction/start already exists from task 006.

## Requirements (Test Descriptions)
- [x] `it closes streams with a reconnect event on shutdown`
- [x] `it cancels every pubsub subscription and timer on shutdown so the event loop can exit`
- [x] `it logs connection counts every log_interval seconds and not at all when log_interval is 0`
- [x] `it registers the broadcasting:serve command`
- [x] `it honours --host and --port options`
- [x] `it stops on SIGINT and SIGTERM`
- [x] `it delivers events through pubsub-redis` (skipped without Redis)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `SocketHttpServer::stop()` runs `onStop` callbacks before awaiting drivers: in `onStop`, write the reconnect frame (see `_plan.md` Shared contracts) and complete every stream body, then cancel hub subscriptions and timers. Anything left referenced keeps `EventLoop::run()` alive forever.
- Follow `PubSubListenCommand` for `EventLoopRunner` usage, but handle SIGTERM as well as SIGINT (docker/systemd send SIGTERM). Signal handlers need ext-pcntl (or ev/uv); fail loudly if unsupported.
- The command resolves `Marko\Log\Contracts\LoggerInterface`, which needs a log driver (e.g. marko/log-file); keep the error loud and mention it in docs.
