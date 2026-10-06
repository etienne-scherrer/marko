# Task 007: Docs pages, READMEs and architecture inventory

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Document presence channels and authorizers per #228's exit criteria.

## Context
- Related files: packages/docs-markdown/docs/packages/broadcasting.md, broadcasting-pusher.md, broadcasting-mercure.md, broadcasting-amphp.md, testing.md, package READMEs, .claude/architecture.md (line ~228 broadcasting inventory row)
- Patterns to follow: docs/DOCS-STANDARDS.md (READMEs are slim pointers)
- broadcasting-pusher.md line ~72 says the 164-character limit includes "the `private-` prefix". Update it for `presence-`.
- broadcasting-pusher.md line ~180 lists `PusherException::presenceChannelsNotSupported()`. Remove it and document the `HttpException` 400/403 responses. Do not promise a JSON error body: the renderer chooses the format, and pusher-js only reads the status.
- broadcasting.md exceptions table (line ~211) and API reference (`Channel`/`PrivateChannel` block, line ~162): add `PresenceChannel`, `isPresence()`, the new `ChannelAuthorizationException` cases and `BroadcastException::presenceChannelsUnsupported()`
- State that one pattern serves exactly one channel kind: registering a private and a presence authorizer on the same pattern raises the duplicate-pattern error
- Note that `PresenceMember::$id` is sent as a string `user_id` in `channel_data` (Pusher requires a string)

## Requirements (Test Descriptions)
- [x] `broadcasting.md documents PresenceChannel, PresenceMember, PresenceChannelAuthorizerInterface and authorizePresence()`
- [x] `broadcasting-pusher.md replaces the not-supported row with a pusher-js presence example and the HttpException error responses`
- [x] `broadcasting-mercure.md and broadcasting-amphp.md state presence is not supported`
- [x] `testing.md notes FakeBroadcaster matches presence vs private channels by kind`
- [x] `READMEs and architecture inventory mention presence channels`

## Acceptance Criteria
- Docs accurate to the implementation

## Implementation Notes
Updated broadcasting.md (Presence Channels section, API reference, exceptions), broadcasting-pusher.md (channel authorization table with HttpException rows, Presence Channels section with pusher-js and Echo examples, presenceChannelAuth signature), broadcasting-mercure.md and broadcasting-amphp.md (presence not supported notes and exception rows), testing.md (FakeBroadcaster kind matching), READMEs for broadcasting and broadcasting-pusher, and the architecture inventory.
