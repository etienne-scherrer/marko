# Task 005: RoadRunner bridge maps uploaded files

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Map PSR-7 `UploadedFileInterface` trees to `Marko\Routing\Http\UploadedFile`, reusing the file RoadRunner already wrote when the stream is file-backed and writing a temp file otherwise; remove those temp files after each request; delete `UploadedFilesNotSupportedException`.

## Context
- Related files: `packages/roadrunner/src/Http/Psr7RequestBridge.php`, `packages/roadrunner/src/Worker/WorkerRequestHandler.php`, `packages/roadrunner/tests/Http/Psr7RequestBridgeTest.php`, `packages/roadrunner/tests/Worker/WorkerRequestHandlerTest.php`

## Requirements (Test Descriptions)
- [x] `it maps a psr7 uploaded file to an UploadedFile`
- [x] `it maps nested and multiple psr7 uploaded files`
- [x] `it maps a failed psr7 upload with its error code`
- [x] `it reuses the file path of a file-backed psr7 upload stream`
- [x] `it removes temporary upload files it created`
- [x] `it removes temporary upload files after each request`

## Acceptance Criteria
- All requirements have passing tests
- `UploadedFilesNotSupportedException` deleted

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
