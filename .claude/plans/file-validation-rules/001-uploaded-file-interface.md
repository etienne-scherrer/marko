# Task 001: UploadedFileInterface in marko/core

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extract the uploaded-file contract into `Marko\Core\Contracts\UploadedFileInterface` so validation can depend on it without depending on routing.

## Context
- Related files: packages/core/src/Contracts/, packages/routing/src/Http/UploadedFile.php
- Patterns to follow: ResettableInterface

## Requirements (Test Descriptions)
- [x] `it declares the uploaded file inspection methods`
- [x] `it is implemented by the routing UploadedFile`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- The interface must declare exactly the methods listed under "Shared Contracts > Interface" in `_plan.md` (tasks 002-005 build against them in parallel). Throwing methods document `@throws MarkoException`, never the routing exception.
- `packages/core/src/Contracts/UploadedFileInterface.php` and `UploadedFile implements UploadedFileInterface` may already exist in the worktree. If so, verify them against the contract and add the tests rather than rewriting.
- No composer.json change is needed: routing and validation already require `marko/core`, and validation keeps `marko/routing` as require-dev only (for tests).
