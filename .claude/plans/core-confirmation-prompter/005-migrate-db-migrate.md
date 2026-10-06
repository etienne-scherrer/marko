# Task 005: Migrate db:migrate to the core prompter

**Status**: completed
**Depends on**: 002, 004
**Retry count**: 0

## Description
`MigrateCommand` uses `Marko\Core\Command\ConfirmationPrompterInterface`; delete the database-local interface, implementation, binding and tests; switch tests to FakeConfirmationPrompter.

## Context
- Related files: packages/database/src/Command/MigrateCommand.php, packages/database/module.php, packages/database/tests/Command/{Helpers.php,MigrateCommandTest.php,StdinConfirmationPrompterTest.php}, packages/database/tests/Unit/ModuleBindingsTest.php

- Remove the `$output->write('Generate a migration with these changes? [y/N] ')` line in `confirmDestructiveChanges()`. The core prompter writes the question and hint itself, so keeping it prints the question twice. Call `$this->confirmationPrompter->confirm('Generate a migration with these changes?', false)`.
- Keep the refusal check on `$this->confirmationPrompter->isInteractive()`, which covers both the TTY and `--no-interaction`, not on `$input->isInteractive()`.

## Requirements (Test Descriptions)
- [ ] `it asks before generating destructive changes when interactive` (asks "Generate a migration with these changes?" default no)
- [ ] `it refuses destructive generation without --force when not interactive`
- [ ] `it cancels generation when the user declines`
- [ ] `it does not bind a confirmation prompter in the database module`

## Acceptance Criteria
- Existing MigrateCommand tests pass using FakeConfirmationPrompter
- Old files deleted

## Implementation Notes
(Left blank - filled in by programmer during implementation)
