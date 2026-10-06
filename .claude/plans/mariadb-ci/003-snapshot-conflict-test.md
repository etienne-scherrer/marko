# Task 003: Real 1020 snapshot-conflict integration test

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove against a real MariaDB 11.8 (`innodb_snapshot_isolation=ON`) that a `REPEATABLE READ` write to a row changed after the snapshot raises `1020`, that it surfaces as `SerializationFailureException`, and that `transaction(attempts: 2)` retries it to success. Skipped on MySQL, which never raises 1020 for this case.

## Context
- Related files: packages/database-mysql/tests/Integration/ConcurrencyErrorsTest.php
- Two connections in one process suffice: the conflicting write does not block, it fails immediately.

## Implementation Constraints
- Skip on MySQL with `IntegrationDatabase::isMariaDb($connection)` (task 001), with a reason naming the difference. Don't sniff versions ad hoc.
- Don't rely on server defaults. On the conflicting session, run `SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ` and `SET SESSION innodb_snapshot_isolation = ON` before beginning.
- Under REPEATABLE READ the snapshot is taken at the first consistent read, not at BEGIN. Inside the transaction, SELECT the row first, then have the other connection (autocommit) UPDATE and commit it, then UPDATE the row from the snapshot session to get 1020.
- Retry test: only attempt 1 may provoke the conflict (guard on an attempt counter). Assert `attempts === 2`, the final row value, and `transactionLevel() === 0`.
- `afterEach` must `reset()` both connections (the session variables persist on the connection) and drop the table.

## Requirements (Test Descriptions)
- [ ] `it raises SerializationFailureException when MariaDB detects a snapshot conflict`
- [ ] `it retries a MariaDB snapshot conflict to success with transaction attempts`

## Acceptance Criteria
- Passes on MariaDB 11.8, skips on MySQL 8.4

## Implementation Notes
(Left blank - filled in by programmer during implementation)
