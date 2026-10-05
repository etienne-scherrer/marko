# Task 004: Resettable PgSqlConnection and MySqlConnection

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
The shared connection lives for the whole worker under marko/roadrunner. Implement `ResettableInterface` so a transaction abandoned by a failed request is rolled back between requests, mirroring `ReadWriteConnection::reset()`.

## Context
- Related files: packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-mysql/src/Connection/MySqlConnection.php
- Keep the change to the implements clause plus one method (#176/#177 edit these classes)

## Requirements (Test Descriptions)
- [x] `it implements ResettableInterface`
- [x] `it rolls back an open transaction on reset`
- [x] `it does nothing on reset when no transaction is open`
- [x] `it does not open a connection on reset when never connected`

## Acceptance Criteria
- All requirements have passing tests for both drivers

## Implementation Notes
(Left blank - filled in by programmer during implementation)
