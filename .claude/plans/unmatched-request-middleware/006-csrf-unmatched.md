# Task 006: CSRF unmatched-request tests

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove a globally registered `CsrfMiddleware` no longer turns unknown paths or wrong methods into 419.

## Context
- Related files: packages/security/tests/

## Requirements (Test Descriptions)
- [x] `it returns 404 rather than 419 for a POST to an unknown path`
- [x] `it returns 405 with Allow rather than 419 for a POST to a GET-only path`
- [x] `it still rejects a POST without a token to a matched route with 419`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
