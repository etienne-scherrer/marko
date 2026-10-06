# Task 003: DatabaseSessionHandler validateId/updateTimestamp

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Inject `SessionConfig` into `DatabaseSessionHandler` and implement `validateId()` (`SELECT 1 FROM sessions WHERE id = ? AND last_activity >= ?`) and `updateTimestamp()` (`UPDATE sessions SET last_activity = ? WHERE id = ?`, payload untouched, never inserts).

## Context
- Related files: packages/session-database/src/Handler/DatabaseSessionHandler.php, packages/session-database/tests/Unit/DatabaseSessionHandlerTest.php, packages/session-database/tests/Feature/BotTrafficTest.php, packages/session-database/tests/ModuleTest.php
- Patterns to follow: existing gc() lifetime computation using `$this->clock`

## Requirements (Test Descriptions)
- [x] `it validates an id whose row is within the lifetime`
- [x] `it rejects an id with no row`
- [x] `it rejects an id whose last activity is older than the lifetime`
- [x] `it updates only last_activity when updating the timestamp`
- [x] `it does not insert a row when updating the timestamp`
- [x] `it returns true when updating the timestamp of an id with no row` (returning false makes PHP emit an E_WARNING)

## Notes
- The constructor change touches every `new DatabaseSessionHandler(...)` call: DatabaseSessionHandlerTest (lines ~127, 282, 292, 301) and BotTrafficTest (line ~137). module.php binds by class name and autowires, so it needs no change.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
