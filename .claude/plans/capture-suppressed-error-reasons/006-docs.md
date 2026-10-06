# Task 006: Docs pages and testing guide

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Document the changed exception messages and the shared helper.

## Context
- Related files: packages/docs-markdown/docs/packages/mail-smtp.md, cache-file.md, codeindexer.md, core.md; .claude/testing.md (point the `@` rule at `ErrorCapture`)

## Requirements (Test Descriptions)
- [x] mail-smtp docs explain connection and STARTTLS errors carry the OS reason
- [x] cache-file docs describe FileCacheException on directory/write failures, including the behaviour change: `set()`/`setMultiple()`/`increment()` now throw `FileCacheException` (extends `CacheException`, no longer `RuntimeException`) instead of returning `false` / throwing `RuntimeException`
- [x] codeindexer docs mention `IndexCacheException::cacheNotRemovable()` thrown by `invalidate()`
- [x] codeindexer docs mention the reason in cache write failures
- [x] core docs / testing guide reference `ErrorCapture`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
Completed. See the PR description for design decisions (reason formats, FileCacheDriver now throws FileCacheException instead of returning false, StreamSocket::enableTls() throws tlsFailed itself and no longer passes the invalid `socket:` named argument).
