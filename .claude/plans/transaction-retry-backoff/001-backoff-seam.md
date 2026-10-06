# Task 001: Backoff seam and interface signature in marko/database

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `SleeperInterface`, `UsleepSleeper` and `TransactionBackoff` to `marko/database`, add the `$backoff` parameter to `TransactionInterface::transaction()`, loud exceptions for negative delays, container bindings, and update every test fixture that implements the interface.

Changing the interface fatals every implementer with the old signature, so this task ALSO adds `int|Closure|null $backoff = null` to the production implementers. Signatures only, no sleeping yet:
- `MySqlConnection::transaction()` and `PgSqlConnection::transaction()` accept `$backoff` (it can be ignored until 003/004).
- `ReadWriteConnection::transaction()` forwards `$backoff` to `$this->write->transaction($callback, $attempts, $backoff)`.
The suite must be green at the end of this task.

Follow the "Interface Contract" section in `_plan.md` exactly: class names, method signatures, the exception named constructors (TransactionBackoff keeps constructor defaults for direct construction).

## Context
- Related files: packages/database/src/Connection/TransactionInterface.php, packages/database/src/Exceptions/TransactionException.php, packages/database/module.php, packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-pgsql/src/Connection/PgSqlConnection.php, packages/database-readwrite/src/Connection/ReadWriteConnection.php
- Implementers to update (grep `function transaction(`): database/tests/{Seed/SeederRunnerTest, Repository/RepositoryBatchInsertTest, Feature/TransactionTest, Feature/Helpers, Feature/DatabaseTestHelperTest}, database-mysql/tests/Query/RecordingTransactionalConnection, database-pgsql/tests/Query/RecordingTransactionalConnection, testing/src/Database/TestDatabase, testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection, testing/tests/Feature/Database/RefreshDatabaseTest, admin-auth/tests/Unit/Repository/RoleRepositoryTest, database-readwrite/tests/{Module/ModuleBootTest, Integration/PgSqlWiringTest, Integration/MySqlWiringTest, Connection/ReadWriteConnectionTest}, queue-database/tests/{Fixtures/SqliteConnection, DatabaseQueueTest}
- Patterns to follow: TransactionException named constructors

## Requirements (Test Descriptions)
- [x] `it waits a random delay between zero and the base delay after the first failed attempt`
- [x] `it doubles the upper bound of the default delay with each failed attempt`
- [x] `it caps the default delay at 500 milliseconds`
- [x] `it waits a fixed number of milliseconds when backoff is an int`
- [x] `it passes the failed attempt number and the conflict to a closure backoff`
- [x] `it rejects a negative int backoff`
- [x] `it rejects a closure backoff that returns a negative delay`
- [x] `it rejects a closure backoff that returns a non-integer`
- [x] `it sleeps once with zero when backoff is zero`
- [x] `it binds SleeperInterface and TransactionBackoff in the module`
- [x] `it resolves TransactionBackoff from a container with the database module bindings`
- [x] `it forwards the backoff from ReadWriteConnection to the write connection` (signature pass-through)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented per the contract in _plan.md. TransactionBackoff keeps constructor defaults so it can be built directly; the container uses the closure binding. The default bound doubles up to the cap, so huge attempt numbers never overflow. Fixtures gained the parameter (plus `use Closure;` in namespaced files).
