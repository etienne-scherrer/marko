# Task 005: Integration Across File, Database and RoadRunner Paths

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Prove end to end, with the real `Session`, `SessionMiddleware` and router, that replaying an unknown cookie creates nothing for both shipped handlers, and that the RoadRunner worker path (one process, `reset()` between requests) behaves the same.

## Context
- Related files: packages/session-file/tests, packages/session-database/tests/Feature/BotTrafficTest.php, packages/roadrunner/tests/StateLeakSpikeTest.php, packages/roadrunner/tests/Support/InProcessRequestHarness.php
- Patterns to follow: BotTrafficTest harness; StateLeakSpikeTest harness usage

## Requirements (Test Descriptions)
- [x] `it creates no session file when an unknown cookie is replayed repeatedly`
- [x] `it resumes a known file session and refreshes its timestamp without rewriting it`
- [x] `it creates no session row when an unknown cookie is replayed repeatedly`
- [x] `it refreshes last activity without rewriting the payload for a resumed unmodified session`
- [x] `it discards an unknown session cookie across worker requests without storing it`
- [x] `it resumes an issued session cookie across worker requests`

## Notes
- An unchanged payload is byte-identical whether PHP calls `write()` or `updateTimestamp()`, so "without rewriting" can't be asserted from stored data. Wrap the real handler in a recording decorator (implementing `SessionHandlerInterface` and delegating) or use a spy connection, then assert `updateTimestamp` was called and `write` was not.
- Use a clock-advanced setup (or touch the file / update `last_activity` into the past) to prove the timestamp actually moved.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
