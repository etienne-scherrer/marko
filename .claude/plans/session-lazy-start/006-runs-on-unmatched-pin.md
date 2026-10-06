# Task 006: Pin no RunsOnUnmatched on session and queued-cookie middleware

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Per the issue's recommendation, do not opt `QueuedCookiesMiddleware` into `#[RunsOnUnmatched]`. Pin it (and `SessionMiddleware`) with tests and document that 404/405 pages have no session or user.

## Context
- Related files: packages/authentication/src/Middleware/QueuedCookiesMiddleware.php, packages/session/src/Middleware/SessionMiddleware.php, packages/docs-markdown/docs/packages/routing.md

## Requirements (Test Descriptions)
- [x] `it does not run on unmatched requests` (QueuedCookiesMiddleware)
- [x] `it does not run on unmatched requests` (SessionMiddleware)
- [x] routing.md "Which middleware runs" notes error pages for unmatched requests have no session or user

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes (from review)
- This task runs in parallel with task 002, which rewrites packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php. Put the pin tests in NEW files (e.g. packages/session/tests/Unit/Middleware/SessionMiddlewareUnmatchedTest.php, packages/authentication/tests/Unit/Middleware/QueuedCookiesMiddlewareUnmatchedTest.php) so the two don't conflict.
- For the existing pattern, see packages/authentication-token/tests/Middleware/TokenRequestMiddlewareTest.php and packages/cors/tests/CorsGlobalTest.php. The routing.md target section is "#### Which middleware runs" (line ~207).
