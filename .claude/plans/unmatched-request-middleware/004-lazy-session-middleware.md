# Task 004: Lazy session persistence in SessionMiddleware

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
`SessionMiddleware` saves the session and sets the cookie only when the request carried a session cookie or modified the session; otherwise it discards the session and sets no cookie.

## Context
- Related files: packages/session/src/Middleware/SessionMiddleware.php, packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php

## Requirements (Test Descriptions)
- [x] `it discards an untouched new session without saving it`
- [x] `it sets no session cookie when a new session is never modified`
- [x] `it saves a new session and attaches the cookie once it is written to`
- [x] `it saves an existing session on every request even when unmodified`
- [x] `it still persists or discards the session when the handler throws`
- [x] `it treats an invalid inbound session cookie as no cookie and discards an untouched session`
- [x] `it emits no cookie at all (neither fresh nor expired) when the session is discarded`
- [x] Update existing `SessionMiddlewareTest` cases that expect a cookie/save from an untouched new session (lines ~153, 183, 206, 223, 242, 259, 304) so the handler writes a value first, or rewrite them to the new semantics
- [x] Update `packages/roadrunner/tests/Integration/EndToEndTest.php` "sets a session cookie on the first request and not on the second" (~line 114) — `/session/read` never writes, so no cookie is set anymore; use `/session/write` for the first request (this test is `integration-destructive` and not run by `composer test`; run it via `composer test:all`)

## Implementation Constraints
- "Inbound cookie exists" means the session actually resumed it: `$inboundId !== null && $this->session->getId() === $inboundId` after start. A malformed cookie rejected by `seedSessionId()` counts as no cookie.
- On the discard path, return the response WITHOUT calling `attachSessionCookie()` (it would emit a fresh cookie for the generated id, or an expired one if the id were cleared).
- Persist/discard decision must happen in the `finally` so exceptions still close the session.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
