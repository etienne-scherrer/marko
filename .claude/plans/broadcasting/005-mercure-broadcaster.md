# Task 005: MercureBroadcaster publish

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
POST form-encoded updates to the hub with a publisher JWT (signed from the key, or a static token from config). Tests use a local HttpClientInterface double (#175 not merged).

## Requirements (Test Descriptions)
- [x] `it posts topic, data, type and id form fields to the hub url`
- [x] `it marks private channel updates with private=on`
- [x] `it sends a bearer publisher jwt with a publish claim`
- [x] `it uses the static publisher jwt when configured`
- [x] `it prefixes topics with the configured topic prefix`
- [x] `it throws BroadcastException when the hub request fails`
- [x] `it broadcasts once per channel when dispatching a broadcastable`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
