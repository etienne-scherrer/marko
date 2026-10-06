# Task 003: Docs and README

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Document multi-file uploads and cookie scoping in the testing docs page; update the README if its quick example changes.

## Context
- Related files: packages/docs-markdown/docs/packages/testing.md, packages/testing/README.md

## Requirements (Test Descriptions)
- [x] `docs describe form-notation uploads and withFiles`
- [x] `docs describe cookie path, domain and secure matching and cookieJar`
- [x] `API reference lists the new signatures`
- [x] `docs warn that session.cookie.secure defaults to true, so session flows over http:// URLs lose the session cookie; use https:// URLs (e.g. get('https://localhost/...')) or set secure false in the test config`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
