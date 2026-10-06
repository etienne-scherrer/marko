# Task 001: Broadcasting interface package core types

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Create packages/broadcasting (composer.json, LICENSE, .gitattributes, known-drivers.php) with Channel, PrivateChannel, BroadcasterInterface, BroadcastableInterface, BroadcastException and NoDriverException. Pattern: packages/pubsub.

## Requirements (Test Descriptions)
- [x] `it creates a public channel with a name`
- [x] `it treats a private channel as a channel subclass`
- [x] `it rejects an empty channel name`
- [x] `it defines BroadcasterInterface with broadcast and dispatch methods`
- [x] `it defines BroadcastableInterface with channels, event and payload methods`
- [x] `it lists the mercure and pusher drivers in known-drivers.php`
- [x] `it suggests composer require commands for every known driver in NoDriverException`
- [x] `skeleton suggest block contains all broadcasting drivers`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
