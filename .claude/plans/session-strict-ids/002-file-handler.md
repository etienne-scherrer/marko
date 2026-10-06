# Task 002: FileSessionHandler validateId/updateTimestamp

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Implement `validateId()` (file exists and its mtime is within the configured lifetime, measured with the injected clock) and `updateTimestamp()` (touch an existing file to the clock's now, never create one) in `FileSessionHandler`.

## Context
- Related files: packages/session-file/src/Handler/FileSessionHandler.php, packages/session-file/tests/Unit/FileSessionHandlerTest.php
- Patterns to follow: existing gc() lifetime comparison using `$this->clock`

## Requirements (Test Descriptions)
- [x] `it validates an id whose session file exists within the lifetime`
- [x] `it rejects an id with no session file`
- [x] `it rejects an id whose session file is older than the lifetime`
- [x] `it updates the session file timestamp without changing its payload`
- [x] `it does not create a session file when updating the timestamp of an unknown id`
- [x] `it returns true when updating the timestamp of an id whose file no longer exists` (returning false makes PHP emit an E_WARNING)
- [x] `it stamps the session file mtime with the clock on write` (`touch($path, $now)` after writing, so validateId/gc/updateTimestamp all use one time source)
- [x] `it sees a fresh mtime after the file changes within the same process` (call `clearstatcache(true, $path)` before `file_exists`/`filemtime` in validateId; stat cache persists across requests in RoadRunner workers)

## Notes
- Lifetime boundary: valid when `mtime >= now - lifetime*60`, mirroring gc's `<` delete condition.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
