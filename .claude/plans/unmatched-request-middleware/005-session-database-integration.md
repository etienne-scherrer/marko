# Task 005: Session-database integration tests

**Status**: completed
**Depends on**: 001, 004
**Retry count**: 0

## Description
Prove end to end, through the Router with a real `Session` and `DatabaseSessionHandler` on a recording connection, that bot traffic produces no session writes.

## Context
- Related files: packages/session-database/tests/

## Requirements (Test Descriptions)
- [x] `it performs no session write and sets no session cookie for a 404`
- [x] `it creates no session for a matched route that never touches it`
- [x] `it writes the session and sets the cookie for a matched route that stores a value`

## Implementation Constraints
- Configure `session.gc_probability => 0` in the harness (see `packages/session/tests/Feature/StatelessRouteTest.php`) — otherwise `session_start()` GC can issue a DELETE and make "no write" assertions flaky.
- Give the recording connection a unique class name/namespace (`Marko\Session\Database\Tests\Unit\MockConnection` already exists).
- Assert on INSERT/UPDATE statements against the sessions table, and on `$response->cookies()` being empty.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
