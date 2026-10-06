# Task 003: Session tracks modification and can discard

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `isModified()` and `discard()` to `SessionInterface`, implemented in `Session` and `FakeSession`, so the middleware can tell whether a request used the session and close it without writing.

## Context
- Related files: packages/session/src/Contracts/SessionInterface.php, packages/session/src/Session.php, packages/testing/src/Fake/FakeSession.php

## Requirements (Test Descriptions)
- [x] `it reports an untouched started session as not modified`
- [x] `it reports the session modified after a value is set`
- [x] `it reports the session modified after a flash message is added`
- [x] `it reports the session modified after the id is regenerated`
- [x] `it does not write to the handler when the session is discarded`
- [x] `FakeSession tracks modification and discard`
- [x] `it does not report the session modified after reading values or an empty flash bag` (get/has/all, `flash()->all()`, `flash()->get()` with no messages — Inertia calls `flash()->all()` on every render)
- [x] `it can start a fresh session after a discard in the same process` (no "A session is already active")
- [x] `it treats discard on an unstarted session as a no-op`
- [x] Add `isModified()` and `discard()` to the anonymous `SessionInterface` fake in `packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php` (~line 366) — otherwise the session suite fatals on the abstract methods

## Implementation Constraints
- `isModified()` is a snapshot comparison (`$this->data` vs data captured at `start()`) OR'd with an explicit flag set by `regenerate()`/`clear()`; do NOT set a dirty flag on every mutating call (FlashBag syncs even when unchanged).
- `discard()` calls `session_abort()` (handler `close()`, never `write()`), sets `started = false`, and leaves `$this->id` as-is; `SessionMiddleware` (task 004) is responsible for not emitting any cookie.
- Docblock both new interface methods.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
