# Task 006: Docs and READMEs

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Document the new Request API (`json`, `input`, `isJson`, `wantsJson`, `file`, `files`, `hasFile`, `UploadedFile`) in `routing.md`, update parameter resolution docs, replace the roadrunner File Uploads limitation, and update the roadrunner DocsTest.

## Context
- Related files: `packages/docs-markdown/docs/packages/routing.md`, `packages/docs-markdown/docs/packages/roadrunner.md`, `packages/roadrunner/tests/DocsTest.php`, `packages/routing/README.md`

## Requirements (Test Descriptions)
- [x] `it documents that file uploads are mapped to UploadedFile`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
