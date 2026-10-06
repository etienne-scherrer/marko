# Task 003: Replace fixed delays in AmphpSseServerTest with polling

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Replace every `delay()` used as a wait-for-condition in `AmphpSseServerTest` with `Poll::until()` (timeout 5s).

## Context
- Related files: `packages/broadcasting-amphp/tests/Feature/AmphpSseServerTest.php`
- Leave `keeps an idle stream open...` (`delay(3.5)`) and `does not log connection counts when log_interval is 0` (`delay(0.1)`, an absence check) as they are.

## Requirements (Test Descriptions)
- [x] `it logs connection counts every log_interval seconds` polls the logger for the `1 open streams across 1 channels` entry; the `SseTestClient` is assigned to a variable that lives until the assertion (a discarded temporary can be destructed and close its socket)
- [x] `it frees the slot and subscription when the last client disconnects` polls `activeSubscriptions('b.shows.42') === 0` before opening the next stream; the `hasChannel('shows.42')` false and `$next->status === 200` assertions are kept, with `$next` opened after the poll
- [x] `it cancels every pubsub subscription and timer on shutdown so the event loop can exit` polls a single condition covering both subscription counts AND `count(EventLoop::getIdentifiers()) < $before` (`$before` captured before `stop()`); polling only the subscriptions would leave the timer-count race in place
- [x] the `use function Amp\delay;` import remains only if still used (`delay(3.5)` and `delay(0.1)` stay), otherwise remove it (lint)

## Acceptance Criteria
- Tests pass, including repeated parallel runs
- Code follows code standards

## Implementation Notes
Executed directly by the orchestrating agent in TDD order (task files are small and interdependent through one test file). See the PR for stress-test numbers.
