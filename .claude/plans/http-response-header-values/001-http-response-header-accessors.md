# Task 001: HttpResponse header() and headerValues()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add an optional `headerValues` constructor parameter (`array<string, list<string>>`) to `HttpResponse`, plus `header()` and `headerValues()` accessors that match names case-insensitively. When `headerValues` is not given, derive one-element lists from `headers`.

## Context
- Related files: packages/http/src/HttpResponse.php, packages/http/tests/Unit/HttpResponseTest.php
- Patterns to follow: existing readonly value object, constructor promotion

## Requirements (Test Descriptions)
- [x] `it returns every Set-Cookie value intact and in order from headerValues`
- [x] `it matches header names case-insensitively in headerValues`
- [x] `it merges values whose header names differ only in case, in order`
- [x] `it returns an empty list from headerValues for a missing header`
- [x] `it returns the comma-joined value from header matching the name case-insensitively`
- [x] `it returns null from header for a missing header`
- [x] `it derives header values from headers when headerValues is not given`
- [x] `it keeps headers unchanged when headerValues is given`
- [x] `it derives comma-joined headers from headerValues when headers is not given`

## Acceptance Criteria
- All requirements have passing tests
- `headers()` signature and behaviour unchanged

## Implementation Notes
Implemented with TDD; see the PR for #220.
