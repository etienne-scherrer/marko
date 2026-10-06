# Task 004: Adopt clock in session-file and session-database

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `ClockInterface` into `FileSessionHandler` (GC cut-off) and `DatabaseSessionHandler` (`last_activity` and GC cut-off).

## Context
- Related files: packages/session-file/src/Handler/FileSessionHandler.php, packages/session-database/src/Handler/DatabaseSessionHandler.php, their tests and composer.json

## Requirements (Test Descriptions)
- [x] `it garbage collects only files older than max lifetime relative to the clock` (session-file)
- [x] `it keeps files exactly at the max lifetime boundary` (session-file)
- [x] `it stores the clock time as last_activity on write` (session-database)
- [x] `it deletes sessions older than max lifetime relative to the clock` (session-database)

## Acceptance Criteria
- All requirements have passing tests
- Both packages require marko/clock
