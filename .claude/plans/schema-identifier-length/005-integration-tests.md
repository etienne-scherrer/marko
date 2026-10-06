# Task 005: Real-Database Integration Tests

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
In the `integration-services` SchemaDiffSettles suites, apply migrations whose derived names would be over 63 bytes against real PostgreSQL and MySQL servers (CI also runs the MySQL suite against MariaDB), and check the database holds the shortened names and the diff settles.

## Context
- Related files: packages/database-mysql/tests/Integration/SchemaDiffSettlesTest.php, packages/database-pgsql/tests/Integration/SchemaDiffSettlesTest.php
- Long table name: `settle_customer_subscription_events` with column `external_billing_reference_id` (`_unique` name = 72 bytes, `fk_` = 68, `_index` = 71 before shortening)
- Scenario: create `settle_users` and the long table with the column plain (no unique, no references). Then diff against an entity version where the column is `unique: true` and `references: 'settle_users.id'`, apply it, and re-diff. The unique index must come from an ALTER (an existing column becoming unique). A create-time inline UNIQUE never uses the derived name.
- PostgreSQL truncates over-long names with only a NOTICE, and unique indexes and FKs are matched by column, so "diff is empty" passes on PG even WITHOUT the fix. Each test must also assert that the introspected index name and FK name equal `IdentifierName::derive(...)` output (and are ≤ 63 bytes).
- Add `settle_customer_subscription_events` to both `dropTables` lists, BEFORE `settle_users` (MySQL drops without CASCADE, so the child goes first).
- Model the FK part on the existing FK settle tests in each file (settle_members/settle_teams).

## Requirements (Test Descriptions)
- [x] `it adds a unique index and foreign key with over-long derived names and the diff is then empty` (MySQL/MariaDB): also asserts the introspected names equal the shortened names
- [x] `it adds a unique index and foreign key with over-long derived names and the diff is then empty` (PostgreSQL): also asserts the introspected names equal the shortened names

## Acceptance Criteria
- All requirements pass against local PostgreSQL 17, MySQL 8.4 and MariaDB 11.8
- Code follows code standards

## Implementation Notes
Also added a real-database test of the over-long `_index` replacement name (FK column stops being unique). Verified on PostgreSQL 17, MySQL 8.4 and MariaDB 11.8.
