# Task 005: Mercure and amphp reject presence channels

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Throw `BroadcastException::presenceChannelsUnsupported()` (added in task 001; do not edit `BroadcastException.php` here) from the Mercure and amphp broadcasters and subscriber-token services when given a `PresenceChannel`, so presence is never silently treated as private or public.

## Context
- Related files: packages/broadcasting-mercure/src/Driver/MercureBroadcaster.php, Subscriber/MercureSubscriberToken.php, packages/broadcasting-amphp/src/Driver/AmphpBroadcaster.php, Subscriber/AmphpSubscriberToken.php and their tests
- **Ordering rule:** check `isPresence()` BEFORE any `isPrivate()` branch, because a PresenceChannel reports isPrivate() true. Reject right after `Channel::from()`, before the event-name check, the publish, the key check or any `ChannelRegistry` call.
- Entry points that must reject:
  - `MercureBroadcaster::broadcast()` (otherwise publishes with `private=on`)
  - `MercureSubscriberToken::for()` (and so `withAuthorizationCookie()`)
  - `MercureSubscriberToken::subscribeUrl()`, which does NOT call `for()` and would silently emit a topic for a presence channel
  - `AmphpBroadcaster::broadcast()` (otherwise publishes to `private-<name>`)
  - `AmphpSubscriberToken::for()`
  - `AmphpSubscriberToken::streamUrl()`, which maps channels to `private-<name>` before calling `for()`, so reject before mapping
- Driver names for the factory: `'Mercure'`, `'Amphp'` (match each class's existing `DRIVER` constant where present)

## Requirements (Test Descriptions)
- [x] `it throws a clear exception when broadcasting to a presence channel` (Mercure; FakeHttpClient records no request)
- [x] `it throws a clear exception when issuing a subscriber token for a presence channel` (Mercure; registry never called)
- [x] `it throws a clear exception when building a subscribe url for a presence channel` (Mercure)
- [x] `it throws a clear exception when broadcasting to a presence channel` (amphp; nothing published)
- [x] `it throws a clear exception when issuing a subscriber token for a presence channel` (amphp; registry never called)
- [x] `it throws a clear exception when building a stream url for a presence channel` (amphp)

## Acceptance Criteria
- All requirements have passing tests; no HTTP request or publish happens

## Implementation Notes
Added an isPresence() guard right after Channel::from() in both broadcasters (before the event-name check and any publish), and a private rejectPresence() helper in MercureSubscriberToken and AmphpSubscriberToken used by for(), subscribeUrl() and streamUrl() (before the private-name mapping). Registry is never reached: its test stub throws unknownChannel for room.1, so the presence exception proves it. 6 tests added; phpcs, php-cs-fixer and phpstan are clean.
