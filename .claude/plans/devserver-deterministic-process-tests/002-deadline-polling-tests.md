# Task 002: Deadline polling in ProcessManagerTest

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Replace every fixed `usleep()` in `ProcessManagerTest.php` with the `devserverWaitUntil()` helper (created in Task 001) that polls a condition until a deadline, so tests no longer assume the OS is fast under parallel load.

## Context
- Related files: packages/devserver/tests/Helpers.php (from 001), packages/devserver/tests/Process/ProcessManagerTest.php

## Requirements (Test Descriptions)
- [ ] `detects when a process exits unexpectedly` polls instead of sleeping
- [ ] `reports stopped processes as stopped in dev:status` asserts immediately after stop (no sleep)
- [ ] `correctly tracks PID for long-running processes` polls instead of sleeping

## Acceptance Criteria
- No `usleep(`/`sleep(` calls remain in ProcessManagerTest.php
- File passes 200 consecutive runs while the full parallel suite runs alongside

## Implementation Notes
- Use `devserverWaitUntil()` from `packages/devserver/tests/Helpers.php` (autoloaded via root `composer.json` `autoload-dev.files`, added in 001). Do not add helpers to `tests/Pest.php`: it is not loaded from the root suite.
