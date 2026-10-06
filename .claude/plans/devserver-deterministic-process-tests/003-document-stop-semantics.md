# Task 003: Document stop semantics

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Document in the devserver docs page that `ProcessManager::stop()` (used by foreground `Ctrl+C`) waits for the whole process group to exit, escalating to SIGKILL after the stop timeout.

## Context
- Related files: packages/docs-markdown/docs/packages/devserver.md, packages/devserver/README.md

## Requirements (Test Descriptions)
- [ ] Foreground mode section explains `Ctrl+C` waits for every service's process group to exit
- [ ] ProcessManager API section documents the `stopTimeoutSeconds` constructor parameter and stop guarantee

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md
- README stays a slim pointer (no change needed)

## Implementation Notes
- Foreground Mode section and ProcessManager API section updated. README unchanged (already a slim pointer; `dev:down` behaviour is unchanged because it uses `PidFile::killProcessGroup()`).
