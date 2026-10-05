# Task 002: UploadedFile value object

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Create `Marko\Routing\Http\UploadedFile` representing one uploaded file, with a guarded one-time `moveTo()` and loud `UploadedFileException` errors.

## Context
- Related files: `packages/routing/src/Http/UploadedFile.php` (new), `packages/routing/src/Exceptions/UploadedFileException.php` (new), `packages/routing/tests/Http/UploadedFileTest.php` (new)

## Requirements (Test Descriptions)
- [x] `it exposes client filename, client media type, size, error and temp path`
- [x] `it is valid only when the error code is UPLOAD_ERR_OK`
- [x] `it moves the file to the target path`
- [x] `it throws when moving a file a second time`
- [x] `it throws when moving an upload that failed with an error code, naming the error`
- [x] `it throws when reading the contents after the file was moved`
- [x] `it returns the file contents and a readable stream`
- [x] `it detects the real mime type from file contents, ignoring the client media type`
- [x] `it guesses the extension from the real mime type`

## Acceptance Criteria
- All requirements have passing tests
- Not a readonly class; only the moved-state is mutable

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
