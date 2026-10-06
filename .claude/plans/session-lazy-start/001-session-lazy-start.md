# Task 001: Session arm/isAvailable and lazy start

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add an explicit "available but not started" state to `SessionInterface`/`Session`. `arm()` marks the session available; the first accessor call starts it. Unarmed access keeps throwing.

## Context
- Related files: packages/session/src/Contracts/SessionInterface.php, packages/session/src/Session.php, packages/session/tests/Unit/SessionTest.php
- Also must touch (to keep the suite compiling): packages/testing/src/Fake/FakeSession.php, packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php (anonymous double, ~line 631), tests/Integration/PageCacheSessionMiddlewareTest.php (anonymous double, line 20)
- Patterns to follow: existing `ensureStarted()` and lifecycle methods; `createInMemorySessionHandler()->handlerCalls` in SessionTest for handler-call assertions

## Requirements (Test Descriptions)
- [x] `it is not available before it is armed or started`
- [x] `it is available but not started after arm`
- [x] `it makes no handler call when armed`
- [x] `it starts on first get when armed`
- [x] `it starts on first set when armed`
- [x] `it starts on first flash access when armed`
- [x] `it still throws SessionNotStartedException when accessed without being armed`
- [x] `it is no longer available after save, discard or reset`
- [x] `it is no longer available after discard when armed but never started` (disarm must happen BEFORE the `!started` early return in `save()`/`discard()`)
- [x] `it disarms without a handler call when destroyed while armed but not started`
- [x] `it returns an empty id without starting when armed`

## Implementation Notes (from review)
- Adding `arm()`/`isAvailable()` to `SessionInterface` is a fatal error for every implementer. In this task, add them to ALL implementers: `Session` (full behavior), `FakeSession` (minimal: `arm()` sets an armed flag, `isAvailable()` returns `armed || started`; task 003 adds lazy-start behavior and tests), and the two anonymous test doubles (minimal compiling versions; task 002 extends the middleware double). Run the full `composer test` before finishing.
- `start()` while armed starts normally and clears the armed flag. `isAvailable()` = armed || started.
- `destroy()` while armed but not started only disarms (no lazy start, no handler call). `destroy()` on a started session also disarms.
- `getId()` and `setId()` never trigger lazy start. `setId()` is still allowed while armed.
- `isModified()` stays false while armed but not started.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
