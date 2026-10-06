# Task 005: Long-running reset of the armed state

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
A worker (RoadRunner) serving many requests from one process must not carry the armed state or a lazily started session across requests.

## Context
- Related files: packages/session/src/Session.php, packages/roadrunner/tests

## Requirements (Test Descriptions)
- [x] `it clears the armed state on reset`
- [x] `it serves consecutive cookieless requests in one process without leaking session data`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes (from review)
- `Session::reset()` must clear the armed flag. If task 001 already covers this, the first requirement here only needs a confirming test.
- Write the consecutive-request test with `InProcessRequestHarness` (see packages/roadrunner/tests/StateLeakSpikeTest.php): cookieless `/session/write`, then cookieless `/session/read` in the same harness. Assert the second response reports `visits=0`, a different (or empty) id, and `user=guest`.
