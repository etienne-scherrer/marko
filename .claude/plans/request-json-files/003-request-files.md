# Task 003: Request files and $_FILES normalization

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Add a `files` constructor parameter plus `file()`, `files()` and `hasFile()` to `Request`, normalize `$_FILES` in `fromGlobals()`, and make `withRoute()` carry files and JSON through.

## Context
- Related files: `packages/routing/src/Http/Request.php`, `packages/routing/tests/Http/RequestTest.php`

## Requirements (Test Descriptions)
- [x] `it normalizes a single uploaded file from $_FILES`
- [x] `it normalizes multiple uploaded files from a files[] input`
- [x] `it normalizes nested uploaded file inputs`
- [x] `it skips inputs submitted without a file`
- [x] `it returns a file by dot-notation key and null when missing`
- [x] `it throws when file() targets a multi-file input`
- [x] `it reports hasFile for present and missing files`
- [x] `it preserves files and json through withRoute()`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
