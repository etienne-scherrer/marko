# Task 003: QueuedCookiesMiddleware

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Global middleware that hands the inbound request to the jar before `$next` and attaches every queued cookie to the response afterwards.

## Context
- Related files: packages/authentication/src/Middleware/QueuedCookiesMiddleware.php, module.php
- Patterns to follow: `SessionMiddleware`

## Requirements (Test Descriptions)
- [ ] `it gives the inbound request to the cookie jar before the handler runs`
- [ ] `it attaches queued cookies to the response`
- [ ] `it returns the response untouched when nothing is queued`
- [ ] `it flushes the queue after attaching cookies`

## Acceptance Criteria
- All requirements have passing tests
