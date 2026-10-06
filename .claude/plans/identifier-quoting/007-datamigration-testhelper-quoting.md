# Task 007: DataMigration and DatabaseTestHelper quote identifiers

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
`DataMigration::insert()/update()/delete()` and `DatabaseTestHelper::seedTable()/truncateTable()/getTableRowCount()` quote table and column names through the connection.

## Context
- Related files: `packages/database/src/Migration/DataMigration.php`, `packages/database/src/Testing/DatabaseTestHelper.php`, `packages/database/tests/Migration/DataMigrationTest.php`, `DataMigratorIntegrationTest.php`, `MigratorTest.php`, `packages/database/tests/Feature/DatabaseTestHelperTest.php`, `packages/testing/tests/Feature/Database/TestDatabaseTest.php` (`TestDatabase` delegates `seedTable()`/`getTableRowCount()` to `DatabaseTestHelper`)
- File ownership vs. task 006 (runs in parallel): this task owns the files listed above; do not edit Repository tests

## Requirements (Test Descriptions)
- [x] `it quotes the table and columns in DataMigration insert`
- [x] `it quotes SET and WHERE columns in DataMigration update`
- [x] `it quotes the table and WHERE columns in DataMigration delete`
- [x] `it quotes the table and columns in seedTable`
- [x] `it quotes the table in truncateTable and getTableRowCount`

## Acceptance Criteria
- All requirements have passing tests
- Full `composer test` green

## Implementation Notes
- `DataMigrationTest` builds its connection with `$this->createMock(ConnectionInterface::class)` (6 times) and asserts exact SQL with `->with(...)`. An unconfigured `quoteIdentifier` returns `''`, so configure it on each mock (shared helper returning ANSI-quoted names). Check `MigratorTest`/`DataMigratorIntegrationTest` mocks that run DataMigrations the same way.
- `getTableRowCount()` reads `$result[0]['count']`; keep the alias unquoted so the key is unchanged.
