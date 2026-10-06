# Task 004: Database, log, mail and authentication commands

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
These commands hand-parse `getArguments()` for options, which stops working once arguments are positionals only. Migrate them to `hasOption()` / `getOption()` and declare their boolean flags.

## Context
- Related files: packages/database/src/Command/{MigrateCommand,SeedCommand,RollbackCommand}.php, packages/log/src/Command/ClearCommand.php, packages/mail/src/Command/TestCommand.php, packages/authentication/src/Command/ClearTokensCommand.php

## Requirements (Test Descriptions)
- [x] `it accepts --step 2 as well as --step=2 for db:rollback`
- [x] `it accepts --class Name as well as --class=Name for db:seed`
- [x] `it accepts --days 3 for log:clear`
- [x] `it reads the email argument after --subject for mail:test`
- [x] `it declares force, no-generate, verbose and v flags`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
