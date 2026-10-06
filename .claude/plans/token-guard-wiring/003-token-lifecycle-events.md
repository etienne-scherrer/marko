# Task 003: Token lifecycle events dispatched by TokenManager

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add events in `Marko\AuthenticationToken\Event`: `TokenCreatedEvent` (user, token id, name, abilities, expiresAt), `TokenRevokedEvent` (token id), `AllTokensRevokedEvent` (user), `TokenAuthenticationFailedEvent` (guard, reason, token id when known, ip) with a `TokenFailureReason` enum (`Invalid`, `Expired`). `TokenManager` dispatches the lifecycle events through an injected `EventDispatcherInterface`. No event carries the plain-text token.

## Context
- Related files: packages/authentication-token/src/Service/TokenManager.php, tests/Service/TokenManagerTest.php
- Patterns to follow: packages/authentication/src/Event/*.php (extend `Marko\Core\Event\Event`), FakeEventDispatcher

## Requirements (Test Descriptions)
- [x] `it dispatches TokenCreatedEvent with the user, name, abilities and expiry when a token is created`
- [x] `it never includes the plain-text token in TokenCreatedEvent`
- [x] `it dispatches TokenRevokedEvent with the token id when a token is revoked`
- [x] `it dispatches AllTokensRevokedEvent with the user when all tokens are revoked`
- [x] `it exposes the failure reason, guard name and ip on TokenAuthenticationFailedEvent`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
