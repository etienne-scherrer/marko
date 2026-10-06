# Task 004: Media upload path via clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`MediaManager::upload()` prefixes stored paths with `date('Y/m')`. Inject `ClockInterface` and use `$clock->now()->format('Y/m')`.

## Context
- Related files: packages/media/src/Service/MediaManager.php, packages/media/tests/Service/MediaManagerTest.php, packages/media/composer.json
- Patterns to follow: packages/session; FakeClock from marko/testing

## Requirements (Test Descriptions)
- [x] `it stores uploads under the year and month of the injected clock`
- [x] composer.json requires marko/clock

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
