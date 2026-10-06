# Task 004: max_size and min_size rules

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Kilobyte size limits for uploaded files.

## Context
- Related files: packages/validation/src/Rules/

## Requirements (Test Descriptions)
- [x] `it passes for a file at the maximum size`
- [x] `it fails for a file over the maximum size`
- [x] `it fails for a file under the minimum size`
- [x] `it fails for a non-file value`
- [x] `it fails for a file with an upload error`
- [x] `it passes for a file at the minimum size`
- [x] `it accepts fractional kilobytes`
- [x] `it returns the messages from the shared contract`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Follow "Shared Contracts" in `_plan.md`: classes `MaxSize(int|float $kilobytes)` and `MinSize(int|float $kilobytes)`. Bounds are inclusive and 1 KB = 1024 bytes. Message texts are pinned there.
- Compare against `size()` only after `isValid()`. An upload error such as `UPLOAD_ERR_INI_SIZE` fails with the `file` message.
- Do not import anything from `Marko\Routing` in `src/`. Tests can pass any `size` to the `UploadedFile` constructor, so no large files are needed.
