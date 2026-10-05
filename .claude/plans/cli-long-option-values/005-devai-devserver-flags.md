# Task 005: Devai and devserver flag declarations

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Declare boolean flags on `devai:install` (force, update-gitignore, skip-lsp-deps, no-interaction) and `dev:up` (foreground, f, detach, d).

## Context
- Related files: packages/devai/src/Commands/InstallCommand.php, packages/devserver/src/Command/DevUpCommand.php

## Requirements (Test Descriptions)
- [x] `it declares its boolean flags on the Command attribute` (devai:install)
- [x] `it declares its boolean flags on the Command attribute` (dev:up)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
