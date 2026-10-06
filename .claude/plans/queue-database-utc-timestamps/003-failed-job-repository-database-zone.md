# Task 003: DatabaseFailedJobRepository writes and reads in the database zone

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Inject `DatabaseTimezoneConfig` into `DatabaseFailedJobRepository`; store failed_at converted to the database zone and hydrate it back as a time in that zone, so `FailedJob::$failedAt` is the instant that was stored whatever the reader's PHP default timezone.

## Context
- Related files: packages/queue-database/src/DatabaseFailedJobRepository.php, packages/queue-database/tests/DatabaseFailedJobRepositoryTest.php
- Constructor: `DatabaseTimezoneConfig $databaseTimezoneConfig` goes immediately after `$connection`. The module binding is a class string (autowired), so module.php needs no change.
- The constructor change also breaks other callers: `new DatabaseFailedJobRepository($connection)` in tests/DatabaseQueueAttemptsTest.php, tests/Integration/PgSqlRoundTripTest.php and the queue timezone test from task 002. Update them (this task runs after 002 to avoid parallel edits to the same files).

## Requirements (Test Descriptions)
- [x] `it stores failed_at in the database timezone`
- [x] `it reads failed_at back as a time in the database timezone whatever the PHP default timezone`
- [x] `it returns the same instant that was stored`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
failed_at written via `format()` and hydrated via `parse()`; `@throws DateMalformedStringException` on all()/find(). Updated every constructor call site.
