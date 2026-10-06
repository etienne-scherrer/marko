# Task 001: Handler Contract Gains validateId/updateTimestamp

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extend `Marko\Session\Contracts\SessionHandlerInterface` with PHP's `SessionUpdateTimestampHandlerInterface` so strict mode actually rejects unknown ids, and pin the resulting `Session` behaviour with an in-memory handler. Stop echoing attacker-supplied ids in `InvalidSessionIdException`.

## Context
- Related files: packages/session/src/Contracts/SessionHandlerInterface.php, packages/session/src/Session.php, packages/session/src/Exceptions/InvalidSessionIdException.php, packages/session/tests/Unit/SessionTest.php, packages/session/tests/Unit/SessionShutdownHandlerTest.php, packages/session/tests/Feature/StatelessRouteTest.php
- Patterns to follow: existing in-memory handler in SessionTest

## Requirements (Test Descriptions)
- [x] `it requires session handlers to validate ids and update timestamps`
- [x] `it discards a well-formed session id the handler does not know and starts with a fresh id`
- [x] `it resumes a session id the handler knows`
- [x] `it refreshes the timestamp instead of rewriting the payload when a resumed session is saved unchanged`
- [x] `it writes the payload under the new id when a resumed session is regenerated without other changes`
- [x] `it does not include the rejected session id in the invalid session id exception`
- [x] `it enables lazy writes so unchanged sessions are not rewritten` (set `ini_set('session.lazy_write', '1')` in `Session::configure()`; the updateTimestamp path depends on it and php.ini may disable it)

## Notes
- The contract in `SessionHandlerInterface.php` already extends `SessionUpdateTimestampHandlerInterface` in this branch, so all three test doubles (SessionTest, SessionShutdownHandlerTest, StatelessRouteTest `RecordingSessionHandler`) must gain `validateId()`/`updateTimestamp()` here. `validateId()` should return whether the id is in the double's store, not a constant `true`.
- `FileSessionHandler`/`DatabaseSessionHandler` won't satisfy the contract until tasks 002/003 land; that is expected.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
