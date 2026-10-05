# Task 003: Timezone-correct Datetimes

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`DateTimeCast` converts to the database timezone (`database.timezone`, default UTC) before formatting, and constructs read values in that timezone.

## Context
- New: packages/database/src/Config/DatabaseTimezoneConfig.php
- DateTimeCast takes an optional DatabaseTimezoneConfig (autowired by the container)

## Requirements (Test Descriptions)
- [x] `it stores an America/New_York datetime as the UTC instant`
- [x] `it reads a stored datetime back as the same instant`
- [x] `it uses the configured database timezone`
- [x] `it defaults the database timezone to UTC when the config key is absent`
- [x] `it throws a configuration exception for an invalid timezone`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
