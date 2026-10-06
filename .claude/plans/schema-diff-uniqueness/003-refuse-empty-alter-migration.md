# Task 003: MigrationGenerator refuses empty alter migrations

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
An alter diff whose SQL generator output is empty in both directions means the diff and the generator disagree. Throw a
MigrationException naming the table and what the diff reported instead of writing an empty migration.

## Context
- Related files: packages/database/src/Migration/MigrationGenerator.php, packages/database/src/Exceptions/MigrationException.php

## Requirements (Test Descriptions)
- [x] `it refuses to write an alter migration whose up and down are both empty`
- [x] `it names the table and the reported changes in the error`
- [x] `it writes no file when it refuses an alter migration`
- [x] `it writes no create migration either when an alter migration in the same diff is refused`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- `generate()` writes create migrations before it reaches the alter loop, so throwing inside the alter loop would
  leave the create files of the same run on disk (and a re-run would write them again with new timestamps). Generate
  the up/down statements of every create/alter/drop migration first, check every alter for empty-both-ways, and only
  then write files.
- Add a named factory on `MigrationException` (follow the existing `columnChangeNotSupported()` style) with a message
  that names the table and lists the added/dropped/modified column and index names from the `TableDiff`, plus a
  suggestion to report the driver/entity mismatch.
