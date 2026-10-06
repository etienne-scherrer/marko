# Task 003: mimes and mimetypes rules

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`Mimes` compares `guessExtension()` against listed extensions (`jpeg` is an alias of `jpg`). `MimeTypes` compares `mimeType()` with `type/*` wildcards. An empty list throws.

## Context
- Related files: packages/validation/src/Rules/

## Requirements (Test Descriptions)
- [x] `it passes when the sniffed extension is listed`
- [x] `it fails when the sniffed extension is not listed`
- [x] `it ignores a lying client filename and media type`
- [x] `it fails for a non-file and for an upload error`
- [x] `it matches type wildcards`
- [x] `it throws when no types are given`
- [x] `it treats jpeg and jpg as the same extension`
- [x] `it matches extensions and types case-insensitively`
- [ ] `it fails instead of throwing when the temp file is unreadable` (not done by design: an unreadable temp file propagates UploadedFileException; see Implementation Deviations in _plan.md)
- [x] `it returns the messages from the shared contract without reading the file`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Follow "Shared Contracts" in `_plan.md`: constructors `Mimes(string ...$extensions)` and `MimeTypes(string ...$types)`, which throw `InvalidArgumentException` when given no entries (after trimming and dropping blanks). Message texts and fixtures are pinned there too.
- `guessExtension()` returns `jpg` for `image/jpeg`. Normalize both sides so that `jpeg` in the list matches `jpg`. A `null` extension never matches.
- Call `mimeType()`/`guessExtension()` only after `isValid()`, inside `try/catch (MarkoException)`, and return `false` on a catch. Never read `clientFilename()` or `clientMediaType()`.
- Do not import anything from `Marko\Routing` in `src/` (tests may).
