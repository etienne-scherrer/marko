# Task 002: file and image rules

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`File` passes for a valid upload. `Image` additionally requires a sniffed MIME type of jpeg, png, gif or webp (never svg).

## Context
- Related files: packages/validation/src/Rules/

## Requirements (Test Descriptions)
- [x] `it passes for a valid uploaded file`
- [x] `it fails for a value that is not a file`
- [x] `it fails for a file with an upload error`
- [x] `it passes image for a png`
- [x] `it fails image for a text file named and typed as png`
- [x] `it fails image for an svg`
- [x] `it fails for a file that has already been moved`
- [ ] `it fails image instead of throwing when the temp file is unreadable` (not done by design: an unreadable temp file propagates UploadedFileException; see Implementation Deviations in _plan.md)
- [x] `it returns the messages from the shared contract without reading the file`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Follow "Shared Contracts" in `_plan.md`: class names `File` and `Image`, the message texts, the fixture bytes and the no-named-helper rule for tests.
- `Image` allowlist: `image/jpeg`, `image/png`, `image/gif`, `image/webp`. Check `isValid()` first, then call `mimeType()` inside `try/catch (MarkoException)` and return `false` on a catch.
- Do not import anything from `Marko\Routing` in `src/` (tests may).
- For the unreadable case, build an `UploadedFile` over a path that does not exist (error `UPLOAD_ERR_OK`).
