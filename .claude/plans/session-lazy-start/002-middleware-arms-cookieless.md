# Task 002: SessionMiddleware arms cookieless requests

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`SessionMiddleware` arms the session instead of starting it when the request carries no usable session cookie (none, or a malformed value). Requests with a well-formed cookie still start eagerly so resumed sessions keep sliding expiry.

## Context
- Related files: packages/session/src/Middleware/SessionMiddleware.php, packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php, packages/session-database/tests/Feature/BotTrafficTest.php
- Patterns to follow: statement-recording connection in BotTrafficTest

## Requirements (Test Descriptions)
- [x] `it arms instead of starting the session when the request has no session cookie`
- [x] `it starts the session eagerly when the request carries a session cookie`
- [x] `it makes zero handler calls for a cookieless request that never touches the session`
- [x] `it starts, saves and sends a cookie for a cookieless request that stores a value`
- [x] `it reads lazily, stores nothing and sends no cookie for a cookieless request that only reads`
- [x] `it expires a malformed cookie without calling the handler`
- [x] `it does not re-arm or start a session that is already available`

## Acceptance Criteria
- All requirements have passing tests
- Existing SessionMiddlewareTest, BotTrafficTest, session-file StrictSessionIdTest/ModuleTest, tests/Integration/PageCacheSessionMiddlewareTest and roadrunner tests remain green

## Implementation Notes (from review)
- Change the guard from `if (!$this->session->started)` to `if (!$this->session->isAvailable())`. Inside it, try `setId()` on a non-null inbound id; if that succeeds, `start()`; if there is no id or `InvalidSessionIdException` is thrown, `arm()`.
- The anonymous double in SessionMiddlewareTest (task 001 gave it compiling stubs) must model arming: `arm()` records it, and get/set/has/flash/regenerate lazily call the existing start logic (including `onStart`) when armed and not started. Without this, every `writingHandler($session)` cookie test fails because `isModified()` returns `started && modified`.
- Rewrite existing tests that encode eager start for cookieless/malformed requests: "starts session before passing to next handler" (line 15) becomes the arm test, and "ignores an invalid inbound session cookie and starts a fresh session" (line 138, asserts non-empty id) must assert arming plus an expired cookie, or a fresh id only after a write.
- The handler-call assertions (zero handler calls, malformed cookie without handler call, read-only stores nothing) belong in packages/session-database/tests/Feature/BotTrafficTest.php, which uses the real `Session` and the statement-recording connection; the unit double cannot observe handler calls. Tighten "creates no session for a matched route that never touches it" to also assert `$connection->queries` is empty. Add a read-only route to BotTrafficController.
