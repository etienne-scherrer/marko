# Task 004: PusherAuthController presence flow and HttpException errors

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Authorize `presence-*` subscriptions through `ChannelRegistry::authorizePresence()`, returning `auth` and `channel_data`. Replace hand-built error responses with `HttpException` (#254), and remove `PusherException::presenceChannelsNotSupported()`.

## Context
- Related files: packages/broadcasting-pusher/src/Controller/PusherAuthController.php, Exceptions/PusherException.php, tests/Unit/Controller/PusherAuthControllerTest.php
- Patterns to follow: `Marko\Routing\Exceptions\HttpException::badRequest()` / `::forbidden()` (assert status via `getStatusCode()`)
- Exact shapes: see `_plan.md` Interface Contracts
- **Response shape:** `{"auth": "<key:sig>", "channel_data": "<JSON string>"}`. `channel_data` is a string, not a nested object. Encode `['user_id' => (string) $member->id, 'user_info' => $member->info]` (user_id is always a string, per the issue and Pusher's docs) (omit `user_info` when info is `[]`) exactly once, with `JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`. Pass that same string to `presenceChannelAuth()` and return it.
- **Prefix stripping:** `presence-foobar` is authorized as registry name `foobar` (mirror the existing `private-` handling)
- **Exceptions that propagate unchanged** (loud misconfiguration, keep them in `@throws`): `ChannelAuthorizationException` (unknown channel, authorizer kind mismatch), `JsonException` (unencodable member info), `PusherException::missingCredentials()`
- **Existing test file must be rewritten:**
  - The anonymous `ChannelRegistry` stub gains an `authorizePresence()` override. It returns `new PresenceMember(10, ['name' => 'Mr. Channels'])` for `foobar` with a non-null user, `new PresenceMember('7')` for `bare`, and null otherwise. It throws `ChannelAuthorizationException::unknownPresenceChannel()` for `unknown`.
  - Convert the existing `returns 403...`, `returns 400 for a missing or malformed socket id` and `returns 400 for a non-private channel` tests to `HttpException` expectations
  - Delete the `throws for presence channels` test and the `PusherException` import if unused
  - Also convert the missing `channel_name` branch to `HttpException::badRequest()`

## Requirements (Test Descriptions)
- [x] `it returns auth and channel_data for an authorized presence member` (published vector: socket `1234.1234`, channel `presence-foobar`; assert the exact `channel_data` string `{"user_id":"10","user_info":{"name":"Mr. Channels"}}` and an `auth` value computed independently with hash_hmac over `socket:channel:channel_data`)
- [x] `it omits user_info from channel_data when the member has no info`
- [x] `it throws a 403 HttpException when the registry denies a presence channel`
- [x] `it passes a guest to the presence authorizer like a private channel`
- [x] `it propagates ChannelAuthorizationException for an unknown presence channel`
- [x] `it throws a 403 HttpException when the registry denies a private channel`
- [x] `it throws a 400 HttpException for a missing or malformed socket id`
- [x] `it throws a 400 HttpException for a missing channel name`
- [x] `it throws a 400 HttpException for a public channel`
- [x] Existing `returns key and signature for an authorized user` and route-attribute tests still pass

## Acceptance Criteria
- All requirements have passing tests
- No hand-built error responses remain in the controller
- presenceChannelsNotSupported() removed (grep the repo, including docs, for leftover references)
- Class docblock updated from "Private-channel authorization endpoint" to cover presence

## Implementation Notes
Presence flow in PusherAuthController::authorizePresence(); errors are HttpException (400/403). Removed PusherException::presenceChannelsNotSupported() and its docs row.
