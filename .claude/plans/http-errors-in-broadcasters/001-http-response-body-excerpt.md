# Task 001: HttpResponse::bodyExcerpt

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `HttpResponse::bodyExcerpt(int $maxBytes = 500): string` returning the trimmed body capped to `$maxBytes` bytes without splitting a UTF-8 character, followed by a truncation note when cut.

## Context
- Related files: packages/http/src/HttpResponse.php, packages/http/tests/Unit/HttpResponseTest.php

## Requirements (Test Descriptions)
- [x] `it returns the whole body as the excerpt when it fits the limit`
- [x] `it truncates the excerpt to the byte limit and notes how many bytes were cut`
- [x] `it does not split a multibyte character when truncating the excerpt`
- [x] `it trims surrounding whitespace from the excerpt`
- [x] `it returns an empty string as the excerpt of an empty body`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Format contract (tasks 002, 003, 004, 006 assert on this, so do not deviate):
- Trim first, then cap. The cap applies to the trimmed body.
- Cut at `$maxBytes` bytes and back off to the previous UTF-8 character boundary (e.g. `mb_strcut($trimmed, 0, $maxBytes, 'UTF-8')`).
- When cut, append exactly `... [truncated N bytes]`, where N = `strlen($trimmed) - strlen($excerpt)`. The note does not count toward `$maxBytes`.
- An empty or whitespace-only body returns `''`.
