# Task 008: PusherBroadcaster publish

**Status**: completed
**Depends on**: 007
**Retry count**: 0

## Description
POST signed events to {scheme}://{host}:{port}/apps/{app_id}/events.

## Requirements (Test Descriptions)
- [x] `it posts name, channels and json data to the events endpoint`
- [x] `it signs the request with auth query parameters`
- [x] `it prefixes private channels with private-`
- [x] `it rejects channel names with characters Pusher does not allow`
- [x] `it throws BroadcastException when the api request fails`
- [x] `it broadcasts once per channel when dispatching a broadcastable`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
