# Task 002: Add the Poll helper and raise SseTestClient ceilings

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Broadcasting\Amphp\Tests\Support\Poll::until()` that polls a condition on the event loop until it holds or a timeout elapses, throwing a clear error on timeout. Raise the `SseTestClient` wait ceilings from 2.0s to 5.0s.

## Context
- Related files: `packages/broadcasting-amphp/tests/Support/SseTestClient.php`, new `packages/broadcasting-amphp/tests/Support/Poll.php`, new `packages/broadcasting-amphp/tests/Unit/Support/PollTest.php`
- Patterns to follow: `tests/Support/InMemoryPubSub.php` (strict types, no final)

## Requirements (Test Descriptions)
- [x] `it returns as soon as the condition holds`
- [x] `it keeps polling until the condition becomes true`
- [x] `it throws naming the condition and timeout when the condition never holds`
- [x] `it lets event loop callbacks run between polls`
- [x] SseTestClient `waitFor`, `waitForEnd` and header waits default to 5.0s

## Determinism constraints for PollTest
- The timeout test uses a short timeout (e.g. 0.2s) so the suite stays fast.
- Never assert an upper bound on elapsed time anywhere in PollTest (load makes it flaky); assert outcomes, call counts, or lower bounds only.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Executed directly by the orchestrating agent in TDD order (task files are small and interdependent through one test file). See the PR for stress-test numbers.
