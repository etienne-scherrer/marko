# Plan: Pusher Presence Channels

## Created
2026-10-05

## Status
completed

## Objective
Add presence channels to `marko/broadcasting` (channel type, member value object, presence authorizer contract, registry support) and implement them in `marko/broadcasting-pusher`, while the Mercure and amphp drivers reject them loudly.

## Related Issues
Closes #228
Relates to #254 (PusherAuthController error responses converted to `HttpException` here)

## Discovery Notes
- Core has `Channel` (public) and `PrivateChannel`; `ChannelAuthorizerInterface::authorize(): bool`; `ChannelRegistry::authorize(): bool`; `BroadcastChannelDiscovery` requires `ChannelAuthorizerInterface`.
- A class cannot implement both authorizer interfaces (same `authorize` name, incompatible return types), so discovery accepts either interface and the registry checks the authorizer's type for the requested channel kind.
- `PusherAuthController` throws `PusherException::presenceChannelsNotSupported()` for `presence-*` and hand-builds 400/403 JSON responses (#254). The controller is reworked here, so those become `Marko\Routing\Exceptions\HttpException` (routing is already a dependency).
- Mercure/amphp broadcasters and subscriber-token services map `isPrivate()` to their private mechanism; they must reject a `PresenceChannel` explicitly, never treat it as private or public.
- `FakeBroadcaster::broadcastsOf()` matches a `Channel` argument by name and privacy; it must also distinguish presence from private.
- Pusher's documented presence example (key `278d425bdf160c739803`, secret `7ad3773142a6692b25b8`, socket `1234.1234`, channel `presence-foobar`, channel_data `{"user_id":10,"user_info":{"name":"Mr. Channels"}}`) is the known signing vector.

## Scope

### In Scope
- `PresenceChannel` (requires authorization: `isPrivate()` true, `isPresence()` true) and `Channel::isPresence()`
- `PresenceMember` readonly value object (`id: string|int`, `info: array<string, scalar|null>`), rejecting an empty id
- `PresenceChannelAuthorizerInterface`; discovery accepts it; `ChannelRegistry::authorizePresence()`; loud errors when the authorizer kind and channel kind mismatch
- Shared `BroadcastException::presenceChannelsUnsupported()` (added in task 001); Mercure and amphp broadcasters, subscriber tokens, `subscribeUrl()` and `streamUrl()` throw it
- Pusher: `PusherSignature::presenceChannelAuth()`, controller presence flow with `channel_data`, broadcaster `presence-` prefix, removal of `presenceChannelsNotSupported()`, controller errors via `HttpException`
- `FakeBroadcaster` presence-aware matching
- Docs pages and READMEs for broadcasting, -pusher, -mercure, -amphp; architecture inventory row

### Out of Scope
- `PusherBroadcaster::presenceUsers()` (Pusher HTTP API users endpoint): optional follow-up per the issue
- Presence support in Mercure or amphp
- `http_errors => false` on driver HTTP requests (separate bug)

## Success Criteria
- [ ] Every exit criterion in #228 has a passing test
- [ ] Docs updated per #228
- [ ] All tests passing, `composer ci` green with zero PHPStan errors
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | PresenceChannel, PresenceMember and shared BroadcastException factories | - | completed |
| 002 | Presence authorizer contract, discovery and ChannelRegistry::authorizePresence | 001 | completed |
| 003 | Pusher presence signature and broadcaster prefix | 001 | completed |
| 004 | PusherAuthController presence flow and HttpException errors (rewrites existing controller tests) | 001, 002, 003 | completed |
| 005 | Mercure and amphp reject presence channels (broadcasters, tokens, subscribeUrl/streamUrl) | 001 | completed |
| 006 | FakeBroadcaster presence-aware matching | 001 | completed |
| 007 | Docs pages, READMEs and architecture inventory | 001-006 | completed |

## Interface Contracts
All tasks build against these exact shapes (namespace `Marko\Broadcasting` unless noted).

```php
// Task 001
readonly class PresenceChannel extends Channel   // NOT extends PrivateChannel
{
    #[Override] public function isPrivate(): bool { return true; }
    #[Override] public function isPresence(): bool { return true; }
}
// Channel gains: public function isPresence(): bool { return false; }

readonly class PresenceMember
{
    /** @param array<string, scalar|null> $info  @throws BroadcastException */
    public function __construct(public string|int $id, public array $info = []) {} // '' id -> emptyPresenceMemberId()
}

// Task 001 (Exceptions/BroadcastException.php -- only task 001 edits this file)
public static function emptyPresenceMemberId(): self;
public static function presenceChannelsUnsupported(string $driver, string $channel): self;

// Task 002
interface PresenceChannelAuthorizerInterface
{
    /** @param array<string, string> $params */
    public function authorize(?AuthenticatableInterface $user, array $params): ?PresenceMember; // null = deny
}
// ChannelRegistry
public function authorizePresence(string $channelName, ?AuthenticatableInterface $user): ?PresenceMember;
// ChannelAuthorizationException (only task 002 edits this file)
public static function unknownPresenceChannel(string $channelName): self;
public static function presenceAuthorizerRequired(string $channelName, string $authorizerClass): self; // presence channel matched a ChannelAuthorizerInterface
public static function privateAuthorizerRequired(string $channelName, string $authorizerClass): self;  // private channel matched a PresenceChannelAuthorizerInterface

// Task 003 (Marko\Broadcasting\Pusher\Auth)
/** Returns "key:HMAC(secret, socket_id:channel_name:channel_data)". $channelData is the already-encoded JSON string. */
public function presenceChannelAuth(string $socketId, string $channelName, string $channelData): string;
```

Presence auth response (task 004): `{"auth": "<key:sig>", "channel_data": "<JSON string>"}`. `channel_data` is a string, not a nested object. The controller encodes it exactly once (`{"user_id":..,"user_info":{..}}`, `user_info` omitted when empty, flags `JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`). The same string is passed to `presenceChannelAuth()` and returned.

**Ordering rule:** `PresenceChannel::isPrivate()` is true, so every first-party branch checks `isPresence()` before `isPrivate()`: Pusher `channelName()`, Mercure/amphp broadcasters and subscriber tokens, and `FakeBroadcaster`.

## Architecture Notes
- Additive in `marko/broadcasting`; `ChannelAuthorizerInterface` is unchanged.
- `PresenceChannel extends Channel` and reports `isPrivate() === true`, so any third-party driver unaware of presence falls back to the safer private (authorized) handling rather than public. First-party non-presence drivers check `isPresence()` first and throw.
- One authorizer per pattern; the authorizer's interface decides which channel kind it serves.

## Risks & Mitigations
- Controller error body change (`{"error":...}` to the renderer's `{"message":...}`): pusher-js only reads the status; called out in the PR and docs.
- Empty `user_info` encoding as `[]`: omit `user_info` when the member has no info, as the official Pusher SDKs do.
