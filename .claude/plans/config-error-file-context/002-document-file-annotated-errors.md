# Task 002: Document file-annotated config load errors

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Update the config docs page so the `Env` error example shows the `[file: ...]` suffix and the `ConfigLoader` API section says load errors name the file. Keep the edit small: `config.md` is also edited by #344.

## Context
- Related files: packages/docs-markdown/docs/packages/config.md ("Environment Variables" error example, "ConfigLoader" API section), packages/config/README.md (slim pointer; check only)
- Patterns to follow: docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `the Environment Variables error example shows the [file: ...] suffix and that the original exception is getPrevious()`
- [x] `the ConfigLoader API section states that load errors name the file and that non-Marko errors pass through`
- [x] `the package README stays a slim pointer and needs no change`

## Acceptance Criteria
- Docs match the implemented behaviour
- No unrelated edits to config.md

## Implementation Notes
Updated the Environment Variables error example and the ConfigLoader API section in config.md; README unchanged (slim pointer).
