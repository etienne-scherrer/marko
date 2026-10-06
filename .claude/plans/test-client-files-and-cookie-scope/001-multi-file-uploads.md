# Task 001: Multi-file and nested uploads

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Accept form-notation field names in `withFile()` and add `withFiles()`, building the nested shape `Request::normalizeFiles()` produces. Throw instead of overwriting.

## Context
- Related files: packages/testing/src/Http/TestClient.php, packages/testing/src/Exceptions/TestClientException.php, fixture EchoController
- Tests: **packages/testing/tests/Feature/Http/TestClientUploadsTest.php already exists** with these cases (currently red). Make them pass; do not duplicate them in TestClientRequestTest.php.
- Patterns to follow: existing withFile tests

## Requirements (Test Descriptions)
- [x] `it sends two photos[] uploads as a list under photos, in order`
- [x] `it sends documents[passport] under the nested key`
- [x] `it nests list fields inside named fields`
- [x] `it sends a list of files with withFiles`
- [x] `it throws when a non-array field is uploaded twice`
- [x] `it throws when a field is used both as a file and as an array of files`
- [x] `it throws when a list field is later used as a single file`
- [x] `it throws for a malformed upload field name`
- [x] `it removes every temporary upload copy after the request`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Change `$files` property type to `array<string, UploadedFile|array<mixed>>`.
- The cleanup loop in `call()` (`foreach ($files as $file) { $file->tempPath() }`) assumes a flat array and fatals on nested ones. Walk it recursively (e.g. `array_walk_recursive`) so every temp copy is unlinked, including when the request throws.
- Validate the field name and shape conflicts *before* copying the file to a temp path, so a thrown exception does not leak a temp copy.
