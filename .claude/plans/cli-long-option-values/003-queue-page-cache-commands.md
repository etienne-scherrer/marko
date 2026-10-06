# Task 003: Queue and page-cache commands

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Migrate `queue:work` and `queue:retry` to the `Input` option methods and declare their flags. Make `page-cache:purge --tag <tag>` read the tag from the option.

## Context
- Related files: packages/queue/src/Command/{WorkCommand,RetryCommand}.php, packages/page-cache/src/Command/PurgeCommand.php and their tests

## Requirements (Test Descriptions)
- [x] `it works the emails queue for queue:work --queue emails`
- [x] `it declares once as a flag on queue:work`
- [x] `it declares all as a flag on queue:retry`
- [x] `it purges tag homepage for page-cache:purge --tag homepage`
- [x] `it errors when --tag has no value`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
