# Task 005: SSE heartbeat and timeout via clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`SseStream` measures its heartbeat interval and timeout with `time()`. Read time from a `ClockInterface`. `SseStream` is a value object constructed by hand in controllers, so the clock is an optional trailing parameter defaulting to `new SystemClock()` rather than a required one.

## Context
- Related files: packages/sse/src/SseStream.php, packages/sse/tests/SseStreamTest.php, packages/sse/composer.json
- Patterns to follow: FakeClock from marko/testing; the data provider / subscription can advance the FakeClock so the loop is deterministic with pollInterval 0

## Requirements (Test Descriptions)
- [x] `it ends a data provider stream once the injected clock reaches the timeout`
- [x] `it emits a heartbeat once the injected clock reaches the heartbeat interval`
- [x] `it ends a subscription stream once the injected clock reaches the timeout`
- [x] `it reads the system clock when no clock is passed`
- [x] composer.json requires marko/clock

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
