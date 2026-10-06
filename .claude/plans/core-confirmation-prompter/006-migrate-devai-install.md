# Task 006: Migrate devai:install to the core prompter

**Status**: completed
**Depends on**: 002, 004
**Retry count**: 0

## Description
`InstallCommand` uses the core interface and stops reading `no-interaction` itself (core handles it); delete devai's interface, StdinPrompter, binding and tests; add marko/testing to devai require-dev.

## Context
- Related files: packages/devai/src/Commands/InstallCommand.php, packages/devai/module.php, packages/devai/composer.json, packages/devai/tests/Unit/Commands/InstallCommandTest.php, packages/devai/tests/Unit/Process/ConfirmationPrompterTest.php

- Drop `$input->hasOption('no-interaction')` and rely only on `$this->confirmationPrompter->isInteractive()`. Call `confirm($question, true)` as today.
- Existing tests to update: line 198 asserts that the flags contain `no-interaction` (remove it from the expectation). The tests at lines ~247, ~368 and ~388 call `execute()` with `--no-interaction` and an interactive prompter double; switch them to `new FakeConfirmationPrompter(interactive: false)`, or they will now prompt.
- Add `"marko/testing": "self.version"` to devai `require-dev`.

## Requirements (Test Descriptions)
- [ ] `it offers to install the recommended docs driver when interactive`
- [ ] `it skips the docs driver offer when not interactive`
- [ ] `it no longer declares no-interaction as its own flag`
- [ ] `it does not bind a confirmation prompter in the devai module`

## Acceptance Criteria
- Existing InstallCommand tests pass using FakeConfirmationPrompter

## Implementation Notes
(Left blank - filled in by programmer during implementation)
