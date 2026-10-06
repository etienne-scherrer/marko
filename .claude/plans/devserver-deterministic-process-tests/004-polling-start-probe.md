# Task 004: Polling start() probe

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Found during the 200-run stress verification: `throws DevServerException when process fails to start` failed under full-suite load because `start()` slept a fixed 150 ms before checking for a 126/127 exit, and the setsid wrapper had not reached `exec` yet. Replace the fixed sleep with a deadline-bounded poll that returns as soon as the process exits, and make the window configurable.

## Context
- Related files: packages/devserver/src/Process/ProcessManager.php, packages/devserver/tests/Process/ProcessManagerTest.php, packages/docs-markdown/docs/packages/devserver.md

## Requirements (Test Descriptions)
- [x] `it throws DevServerException when process fails to start` (uses a generous probe window)
- [x] `it detects a command that fails after the default probe window when given a longer one`
- [x] `it returns from start as soon as the process exits within the probe window`

## Acceptance Criteria
- Default production behaviour unchanged (0.15 s window), but early exit on process exit
- Docs mention `startProbeSeconds`

## Implementation Notes
- New constructor parameter `startProbeSeconds` (default 0.15). `start()` polls `proc_get_status()` every 10 ms until the process exits or the window passes.
