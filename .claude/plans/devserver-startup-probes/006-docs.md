# Task 006: Docs page and README

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Document how `dev:up` detects startup failures (missing command, not executable, early exit, port in use) in foreground and detached modes, plus the new `ProcessManager` constructor options and methods.

## Context
- Related files: `packages/docs-markdown/docs/packages/devserver.md`, `packages/devserver/README.md`, `docs/DOCS-STANDARDS.md`
- `devserver.md:262-266` already documents `startProbeSeconds` with a "default 0.15 seconds" note. Update it to 0.5 and to describe the pre-flight. Add `statusDirectory` and `serverReadyTimeoutSeconds` to the constructor example, and document `isPortAvailable()`/`waitUntilAccepting()`.
- State that detached mode requires ext-posix, and that any early exit inside the window fails in detached mode, while foreground mode fails only on 126/127.

## Requirements (Test Descriptions)
- [x] `docs page has a startup failure detection section covering foreground and detached modes`
- [x] `docs page lists the new ProcessManager options and methods`
- [x] `README stays a slim pointer per DOCS-STANDARDS`

## Acceptance Criteria
- Docs accurate to the implementation

## Implementation Notes
