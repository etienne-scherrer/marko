# Task 003: Pusher presence signature and broadcaster prefix

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `PusherSignature::presenceChannelAuth()` and map `PresenceChannel` to the `presence-` prefix in `PusherBroadcaster`.

## Context
- Related files: packages/broadcasting-pusher/src/Auth/PusherSignature.php, Driver/PusherBroadcaster.php and their tests
- Patterns to follow: channelAuth(), FakeHttpClient-based broadcaster tests
- Exact signature (see `_plan.md` Interface Contracts): `presenceChannelAuth(string $socketId, string $channelName, string $channelData): string` returns `key:HMAC-SHA256(secret, "$socketId:$channelName:$channelData")`. `$channelData` is the already-encoded JSON string. The signer must NOT encode a member itself, so the controller can sign and return one identical string.
- Signing vector: key `278d425bdf160c739803`, secret `7ad3773142a6692b25b8`, socket `1234.1234`, channel `presence-foobar`, channel_data `{"user_id":10,"user_info":{"name":"Mr. Channels"}}`. Take the expected `auth` value from Pusher's published docs (`278d425bdf160c739803:31935e7d86dba64c2a90aed31fdc61869f9b22ba9d8863bba239c03ca481bc80`, verified against the Pusher auth-signatures docs). Never derive it from the implementation's output.
- `PusherBroadcaster::channelName()` must check `isPresence()` BEFORE `isPrivate()` (a PresenceChannel reports isPrivate() true). Otherwise it would get the `private-` prefix.
- The invalid-name message currently says "including the private- prefix". Make it prefix-agnostic (e.g. "including the private- or presence- prefix").

## Requirements (Test Descriptions)
- [x] `it signs the published Pusher presence channel auth example`
- [x] `it prefixes presence channels with presence-`
- [x] `it counts the presence- prefix toward the channel name length limit` (a 155-character name passes, 156 throws)
- [x] `it requires credentials before signing a presence channel`

## Acceptance Criteria
- All requirements have passing tests
- Existing PusherSignature and PusherBroadcaster tests still pass

## Implementation Notes
Added `PusherSignature::presenceChannelAuth()` (signs the already-encoded channel_data verbatim; reuses `secret()` credential check). `PusherBroadcaster::channelName()` now uses a match checking `isPresence()` before `isPrivate()`; invalid-name message is now "including the private- or presence- prefix". Tests added to the existing Pusher signature and broadcaster test files; phpcs clean.
