# Task 006: Docs and READMEs

**Status**: completed
**Depends on**: 002, 004, 005
**Retry count**: 0

## Description
Update the docs pages and package READMEs to describe the portable option set, validation, `http_errors`, the `guzzle` escape hatch, header flattening, and the new fake.

## Context
- Related files: packages/docs-markdown/docs/packages/http.md, http-guzzle.md, testing.md; packages/testing/README.md (list the new fake, minimal edit); packages/http/README.md and packages/http-guzzle/README.md if they mention options
- Standards: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `http.md documents the portable options table and http_errors`
- [x] `http-guzzle.md documents validation and the guzzle escape hatch, replacing the forwarded options table`
- [x] `testing.md has a FakeHttpClient section`
- [x] `testing README lists FakeHttpClient`

## Acceptance Criteria
- Docs accurate against the implemented API
- Existing README tests still pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
