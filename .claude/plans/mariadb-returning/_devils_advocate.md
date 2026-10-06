# Devil's Advocate Review: mariadb-returning

## Critical (Must fix before building)

- **C1 (001, 003): the plan breaks a documented interface contract.** `ConnectionInterface::supportsReturning()` (packages/database/src/Connection/ConnectionInterface.php:64-71) says "Like driverName(), it must not require a live database connection." database.md:1912 repeats it ("work without a live connection, like `driverName()` and `supportsReturning()`"), and database.md:1897 does too. The plan makes `MySqlConnection::supportsReturning()` connect and run `SELECT VERSION()`. Fix applied: task 001 rewrites the interface docblock, and task 003 corrects the docs lines.

## Important (Should fix before building)

- **I1 (001): the `MySqlServer` binding fallback was not specified or tested.** `database-readwrite` swaps `ConnectionInterface` for a `ReadWriteConnection` instance at boot, and a plugin interceptor can wrap the connection in a proxy. In both cases `instanceof MySqlConnection` is false. The binding has to fall back to `new MySqlServer($connection)`. Fix applied: the closure shape is spelled out, and a test requirement covers the fallback. The task also notes that `server()` survives `disconnect()`/`reset()`, that version-read errors propagate instead of turning into `false`, and that the `MySqlServer` docblock gets updated.
- **I2 (002): the RETURNING path reaches other integration tests that run on MariaDB.** On MariaDB, auto-increment `insertBatch()` moves from `execute()` + `lastInsertId()` to `query("... RETURNING ...")`. That changes what these tests exercise: `ReservedWordIdentifiersTest` (a reserved-word key column in the RETURNING clause and in the result row key) and `ConstraintViolationTest::insertBatch` (the violation is now translated from `query()`), plus the admin-auth and queue-database MySQL integration tests. Fix applied: task 002 requires running the whole group on MariaDB 11.8 and 10.11.
- **I3 (002): an existing test will fail on MariaDB.** `GeneratedPrimaryKeysTest`'s "throws ... on MySQL" test runs unguarded on MariaDB in CI today, and the file header says MariaDB is treated like MySQL. The auto-increment tests also need their own table and entity. Fix applied: task 002 adds the guard, rewrites the header, and adds the extra table.
- **I4 (003): the exception test file and the message constraint were unspecified.** `RepositoryExceptionTest.php` does not exist yet. Three tests match the substring `cannot read a generated key back` (RepositoryTest.php:2519, RepositoryBatchInsertTest.php:1124, GeneratedPrimaryKeysTest.php:83). The driver name is `'mysql'` for MariaDB too, so the wording must not imply the server is MySQL. Fix applied.
- **I5 (003): the docs list was incomplete.** It now also covers database.md:215, 1496, 1897 and 1912, database-mysql.md:53 and its API table at line 223 ("Always `false`"; add `server()`), and roadrunner-state-leaks.md:66 (`MySqlServer` is now owned by the connection). Fix applied.

## Minor (Nice to address)

- Under `ReadWriteConnection`, the write connection's own `MySqlServer` and the container's `new MySqlServer($rw)` are separate instances, so a worker runs two `SELECT VERSION()` queries, possibly one on a replica. This is harmless.
- The first `supportsReturning()` call inside `insertBatch()` runs within `transaction($write)`, so `SELECT VERSION()` executes inside the transaction. This is harmless. In `save()` and the pre-check `assertCanReadGeneratedKey()` it runs before the transaction.
- Tests for task 001 already exist in the worktree (MySqlConnectionTest.php:25-62, SharedConnectionTest.php:169-175). The worker should not duplicate them.

## Questions for the Team

- MariaDB does not formally document that a multi-row `INSERT ... RETURNING` returns rows in insert order, and neither does PostgreSQL. In practice both do. Is relying on it acceptable? The count check (`returningRowCountMismatch`) does not catch reordering.
