# Task 007: db:migrate destructive-change confirmation and --force

**Status**: completed
**Depends on**: 003, 006
**Retry count**: 0

## Description
Before generating a migration that drops columns or indexes, print each destructive statement and require confirmation. Non-interactive runs need `--force`.

## Context
- Related files: MigrateCommand.php, new Command/ConfirmationPrompterInterface.php + StdinConfirmationPrompter.php, module.php
- Patterns to follow: packages/devai/src/Process/StdinPrompter.php

## Requirements (Test Descriptions)
- [x] `it lists each destructive statement before generating`
- [x] `it refuses to generate destructive changes non-interactively without --force`
- [x] `it generates destructive changes non-interactively with --force`
- [x] `it generates destructive changes when the user confirms`
- [x] `it does not generate destructive changes when the user declines`
- [x] `it does not prompt when the diff has no destructive changes`
- [x] `it treats dropped foreign keys as destructive`
- [x] `it exits 1 and writes no migration file when refusing non-interactively`
- [x] `it exits 0 and writes no migration file when the user declines`
- [x] `it binds ConfirmationPrompterInterface to StdinConfirmationPrompter in module.php`
- [x] `it applies the same confirmation when --generate is used outside development`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Depends on 003 only to serialize edits to module.php's `bindings` array.
- Use the existing `SchemaDiff::hasDestructiveChanges()` / `getDestructiveChanges()` (covers DROP COLUMN, DROP INDEX
  and DROP FOREIGN KEY). Check between `calculateDiff()` and `migrationGenerator->generate()` in
  `generateMigrationsFromDiff()`.
- Add `force` to `#[Command(flags: [...])]`.
- The command prints the statement list and the question through its `Output`; the prompter only reads the answer
  (devai's StdinPrompter writes to STDOUT directly, which bypasses the captured Output in tests). Interface:
  `isInteractive(): bool` and `confirm(): bool`. `StdinConfirmationPrompter::isInteractive()` is
  `stream_isatty($stream)`. Default answer is No.
- Non-interactive without `--force`: print the list plus "Re-run with --force to generate these changes.", exit 1.
  User declines: print "Migration generation cancelled.", exit 0. Pending files applied earlier stay applied.
- Unit tests inject a fake prompter; never touch real STDIN.
