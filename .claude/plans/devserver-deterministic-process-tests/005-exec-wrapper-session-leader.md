# Task 005: Make the reported PID the session leader on every /bin/sh

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Found by CI (Ubuntu): dash, used as `/bin/sh` there, forks a lone command instead of exec'ing it. So the PID `proc_open()` reported was a short-lived `sh` parent, not the `posix_setsid()` session leader. Group signals went to a group that didn't exist, `stop()` killed only the wrapper shell, and the service was orphaned in its own session. macOS bash exec-optimizes, which hid this locally.

## Context
- Related files: packages/devserver/src/Process/ProcessManager.php (`wrapWithNewProcessGroup()`), packages/devserver/tests/Process/ProcessManagerTest.php

## Requirements (Test Descriptions)
- [x] `it makes the reported PID the leader of its own process group`

## Implementation Notes
- Prefix the wrapper with `exec`, matching the existing non-pcntl fallback (`exec $command`).
