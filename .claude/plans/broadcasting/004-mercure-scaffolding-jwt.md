# Task 004: Mercure package scaffolding, config and MercureJwt

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Create packages/broadcasting-mercure with config/broadcasting-mercure.php, module.php closure binding (pubsub-pgsql pattern) and an HS256 MercureJwt encoder.

## Requirements (Test Descriptions)
- [x] `it encodes the jwt.io HS256 example token`
- [x] `it base64url-encodes without padding`
- [x] `it rejects an empty signing key`
- [x] `it binds BroadcasterInterface to MercureBroadcaster built from config`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented directly (nested subagents were unavailable) following TDD; see the PR for design notes.
