# Task 003: TruncateDatabase quotes through the connection

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Build the pgsql and mysql TRUNCATE statements with $connection->quoteIdentifier(); keep IdentifierValidator.

## Context
- Related files: packages/testing/src/Database/TruncateDatabase.php, packages/testing/tests/Feature/Database/TruncateDatabaseTest.php, packages/testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection.php
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it quotes pgsql table names with the connection's delimiter`
- [ ] `it quotes mysql table names with the connection's delimiter`
- [ ] `existing pgsql and mysql truncate tests still pass`

## Gotcha (from review)
The fixture `RecordingConnection::quoteIdentifier()` currently always returns `"name"` regardless of `$driver`. Make it driver-aware first (backtick with doubled backticks when `driverName()` is `mysql`, double quote with doubled double quotes otherwise); otherwise the existing mysql assertions (`` TRUNCATE TABLE `shows` `` in TruncateDatabaseTest lines 66, 67, 86) fail once `truncateMySql()` quotes through the connection.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
