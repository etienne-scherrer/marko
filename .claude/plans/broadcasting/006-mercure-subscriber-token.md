# Task 006: MercureSubscriberToken, cookie and subscribe URL

**Status**: completed
**Depends on**: 002, 004
**Retry count**: 0

## Description
Issue subscriber JWTs whose mercure.subscribe claim lists only private topics the user is authorized for (via ChannelRegistry), set the mercureAuthorization cookie via withCookie(), and build the EventSource URL.

## Requirements (Test Descriptions)
- [x] `it includes only authorized private topics in the subscribe claim`
- [x] `it excludes public topics from the subscribe claim`
- [x] `it adds an expiry claim from the configured ttl`
- [x] `it sets the mercureAuthorization cookie on the response`
- [x] `it builds the subscribe url with one topic parameter per channel`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
