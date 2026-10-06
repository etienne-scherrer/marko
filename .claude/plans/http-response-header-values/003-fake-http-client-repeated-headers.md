# Task 003: FakeHttpClient repeated-header test

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove that a faked response with repeated headers exposes them through `headerValues()`. No source change expected in `packages/testing` (keep edits minimal; #227 works there in parallel).

## Context
- Related files: packages/testing/tests/Unit/Fake/FakeHttpClientTest.php

## Requirements (Test Descriptions)
- [x] `it exposes repeated headers of a stubbed response through headerValues`
- [x] `it exposes repeated headers of a queued response through headerValues`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Implemented with TDD; see the PR for #220.
