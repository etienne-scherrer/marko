# Task 007: Pusher package scaffolding, config and PusherSignature

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Create packages/broadcasting-pusher with config/broadcasting-pusher.php, module.php closure binding and PusherSignature for REST request signing and channel auth signatures.

## Requirements (Test Descriptions)
- [x] `it signs the published Pusher REST example`
- [x] `it sorts query parameters before signing`
- [x] `it signs the published Pusher channel auth example`
- [x] `it binds BroadcasterInterface to PusherBroadcaster built from config`
- [x] `it derives the api host from the cluster when no host is configured`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
