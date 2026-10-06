# Task 006: FakeBroadcaster presence-aware matching

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`FakeBroadcaster::broadcastsOf()` matches a Channel argument by name and privacy, so a PresenceChannel and a PrivateChannel with the same name would match each other. Compare the presence flag too.

## Context
- Related files: packages/testing/src/Fake/FakeBroadcaster.php and its test
- Compare both `isPrivate()` and `isPresence()` when the argument is a `Channel`. A plain string still matches by name across all kinds.
- Update the `broadcastsOf()` docblock ("a Channel also matches its privacy") to mention the channel kind

## Requirements (Test Descriptions)
- [x] `it distinguishes presence channels from private channels with the same name` (both directions: a PrivateChannel query does not match a presence broadcast, and the reverse)
- [x] `it matches a presence channel broadcast by plain string name`

## Acceptance Criteria
- Requirements have passing tests

## Implementation Notes
broadcastsOf() now also compares isPresence() for Channel arguments; docblock updated. The string-name test passed immediately (already name-only matching).
