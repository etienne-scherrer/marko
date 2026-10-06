# Task 009: Documentation

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006, 007, 008
**Retry count**: 0

## Description
Document precedence, 405, HEAD, OPTIONS, unmatched requests and global middleware in routing.md; rewrite the CORS preflight section and new config in cors.md; drop CORS from security.md; note the bounded memo in roadrunner-state-leaks.md; update READMEs and the architecture route attribute list.

## Context
- Related files: packages/docs-markdown/docs/packages/{routing,cors,security,roadrunner-state-leaks}.md, packages/*/README.md, .claude/architecture.md

## Requirements (Test Descriptions)
- [x] `it documents route precedence and unmatched-request handling in routing.md`
- [x] `it documents CORS global registration, paths and preflight in cors.md`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
