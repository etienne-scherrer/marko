# Task 009: PusherAuthController private channel auth

**Status**: completed
**Depends on**: 002, 007
**Retry count**: 0

## Description
#[Post('/broadcasting/auth')] controller that authorizes via ChannelRegistry and GuardInterface and returns {auth: key:signature}.

## Requirements (Test Descriptions)
- [x] `it returns key and signature for an authorized user`
- [x] `it returns 403 when the registry denies the channel`
- [x] `it returns 400 for a missing or malformed socket id`
- [x] `it returns 400 for a non-private channel`
- [x] `it throws for presence channels`
- [x] `it registers the route at POST /broadcasting/auth`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
