# Task 002: Channel authorization: attribute, discovery, registry

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add #[BroadcastChannel(pattern)], ChannelAuthorizerInterface, BroadcastChannelDiscovery (module src scan, pattern: packages/layout/src/DiscoveringComponentCollector.php) and ChannelRegistry::authorize(channelName, user).

## Requirements (Test Descriptions)
- [x] `it discovers BroadcastChannel classes in module src directories`
- [x] `it throws when a BroadcastChannel class does not implement ChannelAuthorizerInterface`
- [x] `it throws when two authorizers register the same pattern`
- [x] `it allows a channel when the authorizer returns true`
- [x] `it denies a channel when the authorizer returns false`
- [x] `it passes named pattern parameters to the authorizer`
- [x] `it throws ChannelAuthorizationException for an unknown private channel`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
