# Task 003: Adopt clock in marko/session

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `ClockInterface` into `SessionMiddleware` and use it for session cookie expiry instead of `time()`.

## Context
- Related files: packages/session/src/Middleware/SessionMiddleware.php, its tests, packages/session/composer.json, roadrunner fixture app

## Requirements (Test Descriptions)
- [x] `it sets the session cookie expiry to now plus the configured lifetime`
- [x] `it expires the session cookie relative to the clock when the session is destroyed`

## Acceptance Criteria
- All requirements have passing tests
- marko/session requires marko/clock
