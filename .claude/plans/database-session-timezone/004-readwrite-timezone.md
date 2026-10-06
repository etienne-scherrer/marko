# Task 004: Read/write nodes use database.timezone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`database-readwrite` builds each node's `DatabaseConfig` from a per-node array that has no `timezone`. Pass the top-level `database.timezone` into every node config so the primary and the replicas pin the same zone.

## Context
- Related files: packages/database-readwrite/module.php, packages/database-readwrite/tests/
- `ConfigRepositoryInterface::get()` has no default and throws `ConfigNotFoundException` for a missing key. Guard with `$config->has('database.timezone')`; when the key is absent, leave `timezone` unset so `DatabaseConfig::fromArray()` defaults it to UTC.
- The top-level value always overwrites any per-node `timezone`, so all nodes match `DatabaseTimezoneConfig`.

## Requirements (Test Descriptions)
- [x] `it builds the write and read connections with the top-level database timezone`
- [x] `it builds the nodes in UTC when database.timezone is not set`
- [x] `it overrides a per-node timezone with the top-level database timezone`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
