# Task 002: FakeClock in marko/testing

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `Marko\Testing\Fake\FakeClock implements Psr\Clock\ClockInterface`. The fake is always frozen; only `setNow()`, `travel()` and `travelTo()` move it. `marko/testing` requires `psr/clock` only.

## Context
- Related files: packages/testing/src/Fake/FakeClock.php, packages/testing/composer.json
- Patterns to follow: other fakes in packages/testing/src/Fake, AssertionFailedException

## Requirements (Test Descriptions)
- [x] `it implements the PSR-20 clock interface`
- [x] `it returns the same fixed time on every call`
- [x] `it accepts a DateTimeImmutable or a date string`
- [x] `it moves time forward with travel`
- [x] `it moves time backward with travel`
- [x] `it jumps to an absolute time with travelTo`
- [x] `it replaces the current time with setNow`
- [x] `it passes assertNowIs when the time matches`
- [x] `it fails assertNowIs when the time differs`
- [x] `it throws on a malformed travel modifier`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`freeze()` from the ticket is omitted: the fake is always frozen, so it would be a no-op alias of `now()`.
