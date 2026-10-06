# Task 002: New DevServerException factories

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `invalidHost()`, `serverExited()` and `rollbackFailed()` to `DevServerException` so each failure has its own loud, accurate message.

## Context
- Related files: packages/devserver/src/Exceptions/DevServerException.php

## Requirements (Test Descriptions)
- [x] `it reports an invalid host with the accepted syntaxes`
- [x] `it reports a server that exited before accepting connections with its exit code and output`
- [x] `it names every surviving process and PID when a rollback fails and keeps the original error as previous`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
