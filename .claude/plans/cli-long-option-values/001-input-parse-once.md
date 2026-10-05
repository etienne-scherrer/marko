# Task 001: Parse-once Input with long option values

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Parse argv in the `Input` constructor into positionals and options. Support `--name value`, `--`, repeated options and declared flags, and add `getOptionValues()` / `withFlags()`.

## Context
- Related files: packages/core/src/Command/Input.php, packages/core/tests/Unit/Command/InputOutputTest.php
- Patterns to follow: `Input` stays `readonly`; short-option behavior unchanged.

## Requirements (Test Descriptions)
- [x] `it returns the value of a long option given as --queue emails`
- [x] `it returns the value of a long option given as --queue=emails`
- [x] `it returns true for a long option at the end of input`
- [x] `it returns true for a long option followed by another option`
- [x] `it returns the last value and all values for a repeated option`
- [x] `it treats every token after -- as a positional`
- [x] `it excludes option tokens from positionals in any order when the flag is declared`
- [x] `it lets an undeclared bare long option consume the following non-dash token`
- [x] `it applies declared flags through withFlags without mutating the original input`

## Acceptance Criteria
- All requirements have passing tests
- Existing short-option tests unchanged and passing

## Implementation Notes
(Left blank - filled in by programmer during implementation)
