# Task 007: Documentation and upgrade note

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
Describe the session zone in database.md ("Datetimes and Timezones"), database-mysql.md and database-pgsql.md (Driver-Specific Notes), replace the operational note in queue-database.md, and add an upgrade note for existing `TIMESTAMP` data on non-UTC MySQL servers.

## Context
- Related files: packages/docs-markdown/docs/packages/database.md, database-mysql.md, database-pgsql.md, queue-database.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `database.md explains that the session zone follows database.timezone`
- [x] `database-mysql.md documents the zone tables requirement and the loud failure`
- [x] `database-pgsql.md documents SET TIME ZONE`
- [x] `queue-database.md no longer tells operators to run the server on +00:00`
- [x] `an upgrade note gives a conversion query for existing TIMESTAMP data`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS

## Implementation Notes
(Left blank - filled in by programmer during implementation)
