# Plan: Broadcasting Interface (Mercure, Pusher) and SSE Scoping

## Created
2026-10-05

## Status
completed

## Objective
Add a driver-agnostic realtime broadcasting layer (`marko/broadcasting`) with Mercure and Pusher-protocol drivers so live updates scale without holding PHP-FPM workers, and scope `marko/sse` with docs plus an optional max-connections guard.

## Related Issues
Closes #185 (Relates to #186, which builds a native async driver on top of this)

## Discovery Notes
- Interface/driver split follows `marko/pubsub` (`known-drivers.php`, `NoDriverException` resolved automatically by `Container` via the `Marko\{Segment}\Exceptions\NoDriverException::noDriverInstalled()` convention, `KnownDriversValidator` against skeleton `suggest`).
- Driver config is read through closure bindings in `module.php` (pattern from `packages/pubsub-pgsql/module.php`, ticket #166).
- HTTP goes through `Marko\Http\Contracts\HttpClientInterface`. Supported request options (per `http-guzzle`): `headers`, `body`, `json`, `query`, `timeout`; non-2xx responses throw `HttpException`. #175 (`FakeHttpClient`) is NOT merged, so tests use local anonymous-class doubles.
- Package-level attribute discovery pattern: `packages/layout/src/DiscoveringComponentCollector.php` (`ModuleRepositoryInterface` + `ClassFileParser`).
- Current user comes from `Marko\Authentication\Contracts\GuardInterface` (FakeGuard exists in `marko/testing`).
- `CacheInterface` has `increment($key, $ttl)` but no `decrement()`. The SSE limiter uses per-slot keys: `increment()` returning 1 means the slot was acquired; `delete()` releases it. No `get()` (per #165). This avoids a counter that drifts when a worker dies (slot keys expire on their own TTL).
- `StreamingResponse` is constructed directly by app code, so the limiter is passed explicitly as an optional constructor argument; `SseConnectionLimiter` reads `sse.max_connections` (null = unlimited, no-op).
- Split workflow and `PackagingTest` discover packages dynamically; no count bumps needed. Docs sidebar autogenerates the Packages section.
- Verified test vectors: jwt.io HS256 example; Pusher REST signature example (body_md5 `ec365a775a4cd0599faeb73354201b6f`, signature `da454824…457e6c`); Pusher channel auth example (`278d425bdf160c739803:58df8b0c…51c3a4`).

## Scope

### In Scope
- `marko/broadcasting`: `BroadcasterInterface` (`broadcast()`, `dispatch()`), `Channel`, `PrivateChannel`, `BroadcastableInterface`, `#[BroadcastChannel]`, `ChannelAuthorizerInterface`, `ChannelRegistry` + discovery, exceptions, `known-drivers.php`.
- `FakeBroadcaster` in `marko/testing`.
- `marko/broadcasting-mercure`: `MercureBroadcaster`, `MercureJwt`, `MercureSubscriberToken` (token, cookie, subscribe URL), config.
- `marko/broadcasting-pusher`: `PusherBroadcaster`, `PusherSignature`, `PusherAuthController` (`POST /broadcasting/auth`), config.
- `marko/sse`: `SseConnectionLimiter`, `StreamingResponse` guard (503 + Retry-After), `config/sse.php`, docs section "When to use SSE vs. broadcasting".
- Cross-cutting: root `composer.json`, issue templates, skeleton `suggest`, README package table, architecture inventory, project overview, docs pages, slim READMEs.

### Out of Scope
- WebSocket server, native async SSE server (#186).
- Presence channels (`presence-*`, `channel_data`) — follow-up.
- Pusher `socket_id` exclusion (not part of the driver-agnostic interface).
- Null/log broadcaster (pseudo-functionality).

## Success Criteria
- [x] All exit criteria of #185 met
- [x] All tests passing (`composer ci` green, zero PHPStan errors)
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Broadcasting interface package core types | - | completed |
| 002 | Channel authorization: attribute, discovery, registry | 001 | completed |
| 003 | FakeBroadcaster in marko/testing | 001 | completed |
| 004 | Mercure package scaffolding, config and MercureJwt | 001 | completed |
| 005 | MercureBroadcaster publish | 004 | completed |
| 006 | MercureSubscriberToken, cookie and subscribe URL | 002, 004 | completed |
| 007 | Pusher package scaffolding, config and PusherSignature | 001 | completed |
| 008 | PusherBroadcaster publish | 007 | completed |
| 009 | PusherAuthController private channel auth | 002, 007 | completed |
| 010 | SSE max-connections guard | - | completed |
| 011 | Cross-cutting monorepo updates | 001, 004, 007 | completed |
| 012 | Docs pages and slim READMEs | 001-011 | completed |

## Architecture Notes
- `marko/broadcasting` depends on `marko/core` and `marko/authentication` (for `AuthenticatableInterface`) only; never on a driver.
- Drivers depend on `marko/broadcasting`, `marko/http`, `marko/config` (+ `marko/routing`/`marko/authentication` where needed). Core gains nothing.
- Sibling naming: `Mercure*` / `Pusher*`, identical layout (`src/Driver/{Driver}Broadcaster.php`, `config/broadcasting-{driver}.php`, `module.php` closure binding).
- Both drivers implement `dispatch()` identically: broadcast the event once per channel.
- Channel name patterns: `{param}` segments match `[^.]+`; exact-match otherwise.

## Risks & Mitigations
- Pusher payload/channel limits: validate channel names against the Pusher charset and throw `BroadcastException` with guidance.
- Array cache can't coordinate across processes: `SseConnectionLimiter` refuses the array driver loudly.
