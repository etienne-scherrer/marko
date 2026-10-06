# Task 002: transaction() attempts parameter on the interface and every implementor

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Change `TransactionInterface::transaction(callable $callback): mixed` to `transaction(callable $callback, int $attempts = 1): mixed` and document the retry contract. Update EVERY in-repo implementor in the same task so the suite keeps loading (PHP fatals on an implementor with fewer parameters than the interface):

- `TestDatabase`, all test fakes: add the parameter and pass it through where they delegate.
- `PgSqlConnection` / `MySqlConnection`: add the parameter and throw `TransactionException::invalidAttempts()` when it is below 1. Do not implement the retry loop here; tasks 005 and 006 add it.
- `ReadWriteConnection` (absorbed from former task 007): accept `$attempts` and forward it to the write connection, keeping the sticky-flag behaviour.

## Context
- Related files: packages/database/src/Connection/TransactionInterface.php, packages/testing/src/Database/TestDatabase.php, packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-readwrite/src/Connection/ReadWriteConnection.php (+ tests/Connection/ReadWriteConnectionTest.php), and every fake found by `grep -rln "function transaction(" packages` (about 20 files, including database-readwrite/tests, database-pgsql/tests/Query/RecordingTransactionalConnection.php, database-mysql/tests/Query/RecordingTransactionalConnection.php, queue-database/tests, admin-auth/tests, testing/tests/fixtures, database/tests)
- Also update the code sample in packages/docs-markdown/docs/packages/testing.md if it shows the signature
- `TestDatabase` delegates to its connection; it should pass `$attempts` through.

## Requirements (Test Descriptions)
- [x] `it declares an attempts parameter defaulting to one on TransactionInterface::transaction()`
- [x] `it passes attempts through TestDatabase to the connection`
- [x] `it delegates the attempts argument of transaction to the write connection` (ReadWriteConnection)
- [x] `it delegates one attempt by default` (ReadWriteConnection)
- [x] `it rejects an attempts value below one` (PgSqlConnection and MySqlConnection, before any statement is sent)

## Acceptance Criteria
- Whole suite loads (no signature-compatibility fatal errors), and `composer test` is green
- The drivers accept `$attempts` but do not retry yet (tasks 005 and 006 add the retry)

## Implementation Notes
Interface signature + contract docblock; 22 fake implementors updated mechanically; ReadWriteConnection forwards $attempts (two new tests record the forwarded value). TestDatabase does not implement TransactionInterface (it only resolves it), so nothing to pass through there.
