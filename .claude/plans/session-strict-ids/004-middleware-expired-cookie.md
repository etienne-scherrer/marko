# Task 004: SessionMiddleware Expires Rejected Cookies

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
When the request carried a session cookie that did not resume a session (unknown, expired or malformed id) and nothing was persisted, attach an expired cookie so the client stops replaying it. Add the regression tests the ticket lists for #260's other follow-ups.

## Context
- Related files: packages/session/src/Middleware/SessionMiddleware.php, packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php
- Patterns to follow: existing expiredCookie() / attachSessionCookie()

## Requirements (Test Descriptions)
- [x] `it does not write and sends an expired cookie for a well-formed cookie the store does not know`
- [x] `it keeps the session unmodified for a rejected cookie`
- [x] `it sends an expired cookie for a malformed session cookie`
- [x] `it replaces a rejected cookie with a fresh one when the request writes to the session`
- [x] Regression (create+destroy without cookie): already covered by the existing `emits no cookie at all when a session the client never had is destroyed`. Keep it and don't duplicate it.
- [x] Regression (destroy with cookie): already covered by the existing `expires the session cookie relative to the clock when the session is destroyed`. Keep it and don't duplicate it.

## Notes
- The existing test `treats an invalid inbound session cookie as no cookie and discards an untouched session` asserts `cookies()->toBeEmpty()`, which contradicts the new malformed-cookie behaviour. Update it to expect a single expired cookie and still `$saved === false`.
- `createFakeSession()` keeps the seeded id on `start()`, so it cannot simulate an unknown id. Add an option (e.g. `rejectOnStart: true`) that makes `start()` replace the seeded id with a generated one, matching PHP strict mode.
- Rejected = `$inboundId !== null && !$resumed`. Expire it only when `!$persisted`. When the session was persisted under a new id, `attachSessionCookie()` already sends the fresh cookie.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
