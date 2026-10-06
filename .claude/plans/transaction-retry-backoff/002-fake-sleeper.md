# Task 002: FakeSleeper in marko/testing

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
A recording `SleeperInterface` fake so tests assert retry delays without sleeping.

## Context
- Related files: packages/testing/src/Fake/FakeSleeper.php
- Patterns to follow: FakeClock, AssertionFailedException
- API per `_plan.md` Interface Contract: implements `Marko\Database\Connection\SleeperInterface`; public `array $sleeps`, `clear()`, `assertSlept(int ...$milliseconds)`, `assertNotSlept()`
- `marko/database` is only require-dev/suggest in marko/testing: mention FakeSleeper in the `suggest` text for `marko/database`

## Requirements (Test Descriptions)
- [x] `it records each sleep in milliseconds without sleeping`
- [x] `it passes assertSlept when the recorded delays match`
- [x] `it fails assertSlept when the recorded delays differ`
- [x] `it passes assertNotSlept when nothing was recorded`
- [x] `it fails assertNotSlept after a sleep`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
FakeSleeper exposes `public array $sleeps`, plus `clear()`, `assertSlept(int ...)` and `assertNotSlept()`. marko/testing's suggest text for marko/database mentions FakeSleeper.
