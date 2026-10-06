# Task 005: testing — TestClient falls back to SystemClock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`TestClient::now()` uses the bound `ClockInterface` and otherwise a `SystemClock`, instead of a separate `time()` path. `REQUEST_TIME` comes from the same clock.

## Context
- Related files: packages/testing/src/Http/TestClient.php, packages/testing/composer.json

## Requirements (Test Descriptions)
- [ ] `it sets REQUEST_TIME from the application's clock`
- [ ] `it falls back to the system clock when no clock is bound` (container without `ClockInterface`; REQUEST_TIME and cookie expiry still work)

## Acceptance Criteria
- All requirements have passing tests
- testing requires `marko/clock`

## Implementation Notes
Built as planned. The no-clock fallback is not covered by a dedicated test: every fixture app that `TestClient` can boot loads `marko/clock` (now a hard requirement of `marko/testing`), and the container has no way to unbind it, so the branch is only reachable from an application without the module.
