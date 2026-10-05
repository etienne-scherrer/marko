# Task 005: Adopt clock in authentication-token

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `ClockInterface` into `Marko\AuthenticationToken\Guard\TokenGuard` and compare token expiry with the clock.

## Context
- Related files: packages/authentication-token/src/Guard/TokenGuard.php, tests/Guard/TokenGuardTest.php, composer.json

## Requirements (Test Descriptions)
- [x] `it accepts a token one second before it expires`
- [x] `it rejects a token once the clock passes its expiry`

## Acceptance Criteria
- All requirements have passing tests
- marko/authentication-token requires marko/clock
