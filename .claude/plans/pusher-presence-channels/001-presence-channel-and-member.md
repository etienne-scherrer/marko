# Task 001: PresenceChannel, PresenceMember and shared BroadcastException factories

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add the presence channel type, the member value object a presence authorizer returns, and the two shared `BroadcastException` factories. This task is the only one that edits `BroadcastException.php`; task 005 consumes `presenceChannelsUnsupported()`.

## Context
- Related files: packages/broadcasting/src/Channel.php, PrivateChannel.php, Exceptions/BroadcastException.php, tests/Unit/ChannelTest.php
- Patterns to follow: PrivateChannel (`readonly class`, `#[Override]`)
- Exact shapes: see "Interface Contracts" in `_plan.md`
- `PresenceChannel` extends `Channel` (not `PrivateChannel`) and must be declared `readonly class` (the parent is readonly)
- Add `Channel::isPresence(): bool` returning false; `PresenceChannel` overrides both `isPrivate()` and `isPresence()` to return true
- `PresenceMember` is a `readonly class` with promoted `public string|int $id` and `public array $info = []` (`@param array<string, scalar|null>`); an empty-string id throws `BroadcastException::emptyPresenceMemberId()`
- `BroadcastException::presenceChannelsUnsupported(string $driver, string $channel)` takes the driver name and the channel name. Its message names both. Its suggestion points to `PrivateChannel` or the Pusher driver.

## Requirements (Test Descriptions)
- [x] `it treats a presence channel as an authorized channel subclass` (instanceof Channel, not instanceof PrivateChannel, isPrivate() true)
- [x] `it reports only presence channels as presence channels` (Channel, PrivateChannel false; PresenceChannel true)
- [x] `it normalizes a presence channel without changing its type` (Channel::from returns the same PresenceChannel instance)
- [x] `it rejects an empty presence channel name`
- [x] `it creates a presence member with an id and public info`
- [x] `it defaults presence member info to an empty array`
- [x] `it rejects an empty presence member id`
- [x] `it builds a presenceChannelsUnsupported exception naming the driver and channel`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Added PresenceChannel, PresenceMember, Channel::isPresence(), and BroadcastException::emptyPresenceMemberId()/presenceChannelsUnsupported(). Tests in tests/Unit/ChannelTest.php (presence block).
