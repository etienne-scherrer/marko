# Task 003: broadcasting-mercure and broadcasting-pusher via clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`MercureSubscriberToken` computes the JWT `exp` and cookie expiry with `time()`; `PusherBroadcaster` signs requests with `time()`. Inject `ClockInterface`. Keep the Pusher change minimal (constructor + one call) because #228 edits the same package in parallel.

## Context
- Related files: packages/broadcasting-mercure/src/Subscriber/MercureSubscriberToken.php, packages/broadcasting-pusher/src/Driver/PusherBroadcaster.php, their tests and composer.json
- Patterns to follow: packages/session; FakeClock from marko/testing

## Requirements (Test Descriptions)
- [x] `it sets the subscriber JWT exp from the injected clock`
- [x] `it sets the authorization cookie expiry from the injected clock`
- [x] `it signs the request with the injected clock as auth_timestamp`
- [x] both composer.json files require marko/clock

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
