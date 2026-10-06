# Plan: Native async SSE server (marko/broadcasting-amphp)

## Created
2026-10-05

## Status
completed

## Objective
Ship `marko/broadcasting-amphp`: a `BroadcasterInterface` driver that publishes through `marko/pubsub`, plus a long-running `broadcasting:serve` command that runs an `amphp/http-server` SSE server holding thousands of connections per process.

## Related Issues
Closes #186

## Discovery Notes
- `marko/broadcasting` (#185) gives `BroadcasterInterface`, `Channel`/`PrivateChannel`, `ChannelRegistry`. Siblings `broadcasting-mercure` and `broadcasting-pusher` set the structure: `Driver/{Driver}Broadcaster`, `{Driver}Config` built by a `module.php` closure, `Exceptions/{Driver}Exception extends BroadcastException`, `Subscriber/{Driver}SubscriberToken`.
- `marko/amphp` has `EventLoopRunner` and `PubSubListenCommand` (signal handling pattern). `marko/pubsub` has `PublisherInterface`/`SubscriberInterface`/`Subscription`.
- `RedisSubscription` iterates its inner subscriptions sequentially, so the server opens one `Subscription` per channel and pumps each in its own fiber.
- Pusher marks private channels with a `private-` prefix; this driver does the same on the pubsub channel and in the stream URL, so a public subscriber can never receive a private event and the server can tell which channels need a token without calling back into the app.
- `amphp/http-server` v3: compression middleware buffers bodies and `createForDirectAccess` caps connections, so the server is built with the plain constructor (no compression, no concurrency limit) and enforces its own limits. `Client::onClose()` gives immediate disconnect detection. `SocketHttpServer::stop()` awaits pending responses, so open streams are completed in `onStop` first.
- `marko/log` is not PSR-3; a small PSR-3 bridge forwards http-server logs to it.
- Split workflow and `tests/PackagingTest.php` enumerate `packages/` dynamically, so they need no edits.

## Scope

### In Scope
- `AmphpBroadcaster` (prefix, JSON `{event,data,id}`, generated sortable id, private prefix, pgsql 8000-byte guard)
- `AmphpSignature` HMAC token sign/verify; `AmphpSubscriberToken::for()` and `streamUrl()` via `ChannelRegistry`
- `ReplayBuffer` (bounded, TTL, per-process), `SseEvent` frame formatting
- `ChannelHub` shared subscription + fan-out, `SseConnection`
- `StreamRequestHandler` (stream path, health, CORS, auth 403, limits 503, Last-Event-ID replay/reset)
- `AmphpSseServer` (start/stop, heartbeat, periodic log, graceful shutdown) and `broadcasting:serve` command
- Config, module.php, docs page, README, cross-cutting monorepo updates, load check

### Out of Scope
- WebSocket / Pusher protocol
- Durable or cross-node replay (Redis streams)
- Any change to `marko/amphp`, `marko/pubsub*`

## Success Criteria
- [ ] Every exit criterion of #186 covered by a test
- [ ] Docs page + slim README; broadcasting.md and sse.md cross-link
- [ ] known-drivers, skeleton suggest, root composer, issue templates updated
- [ ] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Package scaffolding, config, exception | - | completed |
| 002 | AmphpBroadcaster | 001 | completed |
| 003 | Token signing and subscriber token | 001 | completed |
| 004 | SseEvent and ReplayBuffer | 001 | completed |
| 005 | ChannelHub and SseConnection | 004 | completed |
| 006 | AmphpSseServer start + StreamRequestHandler core (delivery, heartbeat, health, CORS) + integration harness | 005 | completed |
| 010 | Stream auth (403), limits (503, per-IP, channel cap) and Last-Event-ID replay | 003, 006 | completed |
| 007 | Graceful shutdown, periodic log and serve command | 010 | completed |
| 008 | Cross-cutting monorepo updates | 001 | completed |
| 009 | Docs page and README | 002, 003, 007 | completed |

## Architecture Notes
- Wire format on pubsub: `{"event": string, "data": object, "id": string}` on channel `channel_prefix . [private-]name`.
- Token: `base64url(json{u,c,e}) . "." . base64url(hmac_sha256(app_key, payload))`.
- Replay: every received event gets a process-local sequence. A connection's Last-Event-ID resolves to a sequence S; replay is valid only if each requested channel has been continuously subscribed since before S and has evicted nothing newer than S, else `event: reset`.

### Shared contracts (all tasks build against these)
- Config: `AmphpBroadcastingConfig` + `config/broadcasting-amphp.php` already define every key (host, port, path, health_path, public_url, channel_prefix, app_key, heartbeat, replay_buffer, replay_ttl, max_connections, max_connections_per_ip, allowed_origins, trusted_proxies, token_ttl, log_interval). Do not add keys; anything else is a class constant.
- Pubsub channel: `channelPrefix . (isPrivate ? 'private-' : '') . $channel->name`. Stream URL: `{public_url}{path}?channels=a,private-b&token=...` (rawurlencoded, comma-separated, `private-` marks private channels).
- Token claims: `u` = user auth identifier as a string (empty for guests), `c` = list of private stream channel names (WITH `private-`, exactly as they appear in `?channels=`), `e` = unix expiry. MAC = HMAC-SHA256(app_key, "u|c1,c2|e"), compared with `hash_equals`; `AmphpSignature::verify(string $token): ?AmphpTokenClaims` returns claims or null (forged/expired/malformed). (Task 003 was built before the review; the prefixed form was kept because the server compares requested names directly.)
- SSE frame: `id: {event id}\nevent: {event}\ndata: {json of data only}\n\n`. The SSE `id` is the publisher's event id (not the local sequence); `ReplayBuffer` keeps an id -> sequence index, an unknown id means reset. CR/LF in event names or ids is rejected at publish time and never written to a frame.
- Special frames: heartbeat `:\n\n` (as specified in #186); reset `event: reset\ndata: {}\n\n`; shutdown `event: reconnect\ndata: {}\n\n` preceded by `retry: 1000`.
- amphp gotcha: `DefaultHttpDriverFactory` passes `streamTimeout` (default 15s) as the HTTP/1 idle timeout, refreshed only on body writes. The server MUST build `DefaultHttpDriverFactory` with `streamTimeout` comfortably above `heartbeat` (e.g. `max(60, heartbeat * 3)`) or idle streams are dropped at exactly the default heartbeat.
- Scale gotcha: `RedisSubscriber::subscribe()` opens a NEW Redis connection per call, so one Subscription per channel = one Redis connection per distinct active channel. Hence the per-stream channel cap (task 010) and a docs note (task 009).
- Shared test double: `tests/Support/InMemoryPubSub` (task 005) implements PublisherInterface + SubscriberInterface with fiber-suspending iterators (`Amp\Pipeline\Queue`), counts live subscriptions, and is reused by 006/010/007.

## Risks & Mitigations
- Slow consumers grow memory: bounded per-connection pending frames; exceeding it closes the connection.
- Flaky network tests: in-process server on port 0, explicit timeouts on reads.

## Implementation Notes
- Executed directly in dependency order (after the review the remaining chain 004 → 005 → 006 → 010 → 007 → 009 was strictly sequential), following TDD per task.
- Token `c` claims keep the `private-` prefix (see Shared contracts).
- Disconnect detection: amphp's HTTP/1 driver does not read from a socket while a bodyless request's response streams, so a vanished client is noticed on the next write (an event or the next heartbeat), which fires `Client::onClose()`. The integration test uses a 1s heartbeat.
- Load check (2,000 streams, 10 channels, Redis pub/sub, ext-ev): opened in 0.37s, fan-out to all 2,000 in 66ms, ~70 KB PHP memory per stream (139 MB total, 227 MB RSS). With stream_select (no ev/uv/event) the loop fatals past 1,024 FDs, so the server caps `max_connections` at 1,000 and warns.
