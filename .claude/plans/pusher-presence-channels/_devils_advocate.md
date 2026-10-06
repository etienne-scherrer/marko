# Devil's Advocate Review: pusher-presence-channels

Note: issue #228 could not be fetched (no shell access in this review). The review relies on `_plan.md` and the source.

## Critical (Must fix before building)

### C1. Tasks 001 and 005 both edit `BroadcastException.php` in parallel
Task 001 needs a factory for the empty presence member id (per its Context, `Exceptions/BroadcastException.php`). Task 005 adds `BroadcastException::presenceChannelsUnsupported()`. Both depend only on 001's completion in theory, but 005's factory is a core-package change living in a driver task. If 001's worker also adds an exception there, the two workers produce conflicting edits to the same file, and 005 changes `marko/broadcasting` from a driver task.
**Fix:** move `presenceChannelsUnsupported(string $driver, string $channel)` and `emptyPresenceMemberId()` into task 001. Task 005 only calls the factory.

### C2. Interface contracts are not defined anywhere
No task gives the signature of `PresenceChannelAuthorizerInterface::authorize()`, `ChannelRegistry::authorizePresence()`, `PresenceMember::__construct()`, `PusherSignature::presenceChannelAuth()`, or the new exception factory names. Tasks 003, 005 and 006 run in parallel against 001's API. Task 004 depends on 002 and 003 having picked compatible shapes. Each worker has to guess.
**Fix:** add an "Interface Contracts" section to `_plan.md` and reference it from every task.

### C3. `channel_data` encoding and signing contract (tasks 003, 004)
The Pusher protocol signs `socket_id:channel_name:channel_data` and returns `channel_data` as a **JSON string** field. It is not a nested object. pusher-js passes the string back to the server verbatim, and the server re-verifies the HMAC against it. If the signature method encodes the member itself and the controller encodes it again with different flags or key order, the signatures fail at runtime. Unit tests with a mocked registry would not catch that.
**Fix:** `presenceChannelAuth(string $socketId, string $channelName, string $channelData): string` takes the already-encoded string. The controller encodes once (`JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`, keys `user_id` then `user_info`) and passes the same string to both the signer and the response. The response is `{"auth": "...", "channel_data": "<string>"}`.

### C4. Task 002 fixtures will break existing tests
`ChannelRegistryTest` asserts the exact discovered map for `ValidModule` (`'admin'`, `'shows.{showId}.seats.{seatId}'`) and the exact `patterns()` list. Adding a presence authorizer to `ValidModule` breaks both tests.
**Fix:** add a separate `tests/Fixtures/PresenceModule/src/RoomPresenceAuthorizer.php` (pattern `rooms.{roomId}`). Parameterize the `channelRegistry()` helper by fixture. Use `ValidModule`'s `admin` for the "private-only authorizer on a presence channel" test.

### C5. Task 004 must rewrite the existing controller test file
`PusherAuthControllerTest` stubs `ChannelRegistry` by anonymous subclass, overriding only `authorize()`. Four existing tests assert `Response` status codes (403/400), and one asserts `PusherException 'Presence channels are not supported'`. After the change these tests fail, and the stub has no `authorizePresence()`. The task lists only new test names, so the worker may treat the existing failures as regressions.
**Fix:** state explicitly that the stub gains an `authorizePresence()` override, that the existing status-code tests are converted to `HttpException` expectations (`getStatusCode()`), and that the "throws for presence channels" test is deleted.

## Important (Should fix before building)

### I1. Mercure `subscribeUrl()` bypasses `for()` (task 005)
`MercureSubscriberToken::subscribeUrl()` maps every channel to a topic without calling `for()`. A `PresenceChannel` would silently become a topic URL. `AmphpSubscriberToken::streamUrl()` only rejects presence by accident: `isPrivate()` is true, so it calls `for()` after it has already mapped the channel to `private-<name>`.
**Fix:** reject explicitly in `MercureSubscriberToken::subscribeUrl()` and `AmphpSubscriberToken::streamUrl()`, with tests. Reject before any mapping, key check or registry call.

### I2. `isPresence()` must be checked before `isPrivate()` everywhere (tasks 003, 005, 006)
`PresenceChannel::isPrivate()` returns true. `PusherBroadcaster::channelName()`, `MercureBroadcaster::broadcast()` (`private=on`), `AmphpBroadcaster::channelName()` and both subscriber tokens branch on `isPrivate()` first. If the presence check comes after, presence is silently handled as private. That is the exact bug 005 exists to prevent.
**Fix:** state the ordering rule in the plan and in each task.

### I3. Discovery and registry details (task 002)
- `ChannelDefinition::$authorizerClass` is documented as `class-string<ChannelAuthorizerInterface>`. It must become a union, or PHPStan fails.
- `ChannelRegistry::authorize()` uses `/** @var ChannelAuthorizerInterface */`. That must become `instanceof` checks that throw the mismatch exceptions.
- `notAnAuthorizer()` must name both interfaces. It must keep the substring `must implement ChannelAuthorizerInterface`, which an existing test asserts.
- `unknownChannel()` says "private channel" and suggests `ChannelAuthorizerInterface`. An unknown presence channel needs its own message.
- The `#[BroadcastChannel]` attribute docblock says it registers a `ChannelAuthorizerInterface` for a private pattern. Update it.

### I4. Pusher name-length message (task 003)
`PusherBroadcaster` says "164 characters at most, including the private- prefix". Presence uses a 9-character prefix (max name 155). Make the message prefix-agnostic.

### I5. Controller behaviour for registry exceptions is unspecified (task 004)
`ChannelAuthorizationException` (unknown channel, kind mismatch) and `JsonException` (unencodable member info) have no specified handling. Current behaviour lets unknown channels propagate (500, loud misconfiguration).
**Fix:** propagate unchanged, keep them in `@throws`, and add a test for the unknown-channel path.

### I6. Task 004 should prove the signing vector end to end
Task 003 proves the signer. Task 004 should drive the published vector through the controller: the stub returns `new PresenceMember(10, ['name' => 'Mr. Channels'])` for `foobar`, and the test asserts the exact `channel_data` string and the `auth` value. That catches encoding drift between controller and signer (C3).

### I7. Docs coverage (task 007)
- `testing.md` documents `broadcastsOf()` matching. It should mention presence vs private matching.
- `broadcasting-pusher.md` line 72 mentions the `private-` prefix length rule.
- Docs must state that a pattern serves exactly one channel kind: a private and a presence authorizer on the same pattern raises `duplicatePattern`.

## Minor (Nice to address)
- The expected `auth` value for the presence vector must come from Pusher's published docs, not from running the implementation. Orchestrator note: the value recalled here was wrong; the published value is `278d425bdf160c739803:31935e7d86dba64c2a90aed31fdc61869f9b22ba9d8863bba239c03ca481bc80` (verified by HMAC and against the docs).
- Pusher limits `user_id` length (128 chars) and the size of `user_info`. Neither is validated, so overlong values fail at the Pusher server with a vague client-side error.
- An `int` id is encoded as a JSON number (matching the vector). pusher-js `members.get()` lookups key by string, which is worth a docs note.
- `HttpException` rendering may produce HTML rather than JSON, because pusher-js does not send `Accept: application/json`. pusher-js only reads the status, so this is harmless, but the docs should not promise a JSON error body.
- Task 006 is a single test. It could be folded into 001, but it is a separate package, so it is acceptable.
- `FakeBroadcaster::describe()` does not show the channel kind in assertion messages.
- `BroadcastException::emptyChannelName()` suggestion mentions only `Channel` and `PrivateChannel`.

## Questions for the Team
1. **One pattern, two kinds:** should a single pattern (e.g. `rooms.{id}`) serve both `PrivateChannel` and `PresenceChannel`? Laravel allows this. The current design (one authorizer per pattern, kind decided by interface) forbids it and raises `duplicatePattern`.
2. **Nested `user_info`:** Pusher accepts arbitrary JSON (e.g. an `avatar` object). The plan restricts it to `array<string, scalar|null>`. Keep the restriction, or widen it to JSON-serializable `mixed`?
3. **Unknown presence channel status:** keep it as a loud 500 (`ChannelAuthorizationException`, matching private behaviour), or map it to 403 for the client?
