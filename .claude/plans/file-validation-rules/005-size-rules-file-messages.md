# Task 005: max/min/between file messages

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`Max`, `Min` and `Between` given an uploaded file fail with a message pointing to `max_size`/`min_size` instead of a numeric comparison.

## Context
- Related files: packages/validation/src/Rules/Max.php, Min.php, Between.php

## Requirements (Test Descriptions)
- [x] `it fails max for an uploaded file with a message pointing to max_size`
- [x] `it fails min for an uploaded file with a message pointing to min_size`
- [x] `it fails between for an uploaded file with a message pointing to min_size and max_size`

- [x] `it still counts items for an array of uploaded files`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `passes()` already returns `false` for objects. Keep that behavior; don't add a size comparison. The change is in `message()`: check `$value instanceof UploadedFileInterface` first, before the array/numeric/string branches, and return the texts pinned in "Shared Contracts" in `_plan.md`.
- Only `Max.php`, `Min.php` and `Between.php` (and their tests) change; tasks 002-004 edit other files in parallel.
