# Devil's Advocate Review: identifier-quoting

## Critical (Must fix before building)

1. **PHPUnit mocks/stubs of `ConnectionInterface` will return `''` from `quoteIdentifier()` (tasks 006, 007).**
   16 test files build `ConnectionInterface` with `createMock()` / `createStub()` (72 occurrences): `DataMigrationTest` (6, with exact `->with('INSERT INTO ...')` SQL), `DataMigratorIntegrationTest`, `MigratorTest`, `MigrationTest`, `RepositoryBatchInsertTest`, `RepositoryUpsertTest`, etc. An unconfigured method with a `string` return type returns `''`, so once Repository/DataMigration call `quoteIdentifier()` the SQL collapses to `INSERT INTO  (, ) VALUES ...`. Task 005 only covers hand-written stubs. Fix: tasks 006 and 007 must configure `quoteIdentifier` on every PHPUnit mock/stub they exercise (shared helper returning ANSI-quoted names).

2. **Task 006 scope misses tests outside `packages/database` and names a non-existent method.**
   `Repository::isColumnUnique()` (protected) is what `RoleRepository` uses; there is no `isUnique`. `admin-auth/tests/Unit/Repository/RoleRepositoryTest.php:99,116-117` assert `slug = ?` / `id != ?`, which become `"slug" = ?` / `"id" != ?`. Stubs that pick canned rows by SQL substring (e.g. `str_contains($sql, 'FROM roles')`) will silently return the wrong rows. Fix: widen task 006 to every package whose tests go through `Repository` (admin-auth, testing fixture app, etc.) and require a full `composer test` run.

3. **PermissionRepository integration test placement (task 009).**
   `database-mysql`/`database-pgsql` have no dev dependency on `marko/admin-auth` (which pulls admin, authentication, routing). Putting admin-auth tests in the driver packages inverts the dependency. Putting them in `admin-auth/tests/Integration` means CI never runs them against MariaDB (the MariaDB step only runs `packages/database-mysql/tests/Integration`). Fix: new task 011 places them in `packages/admin-auth/tests/Integration/{MySql,PgSql}/`, adds the drivers to admin-auth `require-dev`, and extends the MariaDB CI step with `packages/admin-auth/tests/Integration/MySql`.

## Important (Should fix before building)

4. **Task 008: `findBy()` returns `EntityCollection`; `findByGroup()` must return `array`.** Use `->toArray()`. `PermissionRepositoryTest.php:166` asserts `` `group` = ? `` and must change to `"group" = ?`.
5. **Task 004: `pg_get_serial_sequence()` table argument.** It parses its first argument as SQL, so the literal must hold `PgSqlIdentifier::quote($table)`. The column argument is taken literally, so it must stay unquoted. The dollar-quote tag scan must cover the whole body, including the RAISE message, which embeds the names.
6. **Tasks 003/004: "no inline quoting" source-scan tests will false-positive.** `MySqlIntrospector` keeps backticks in the `json_valid` regex and docblocks; `PgSqlGenerator` embeds names in RAISE message text. Scope the assertions to quoting concatenations (`'`' .`, `"\"$`), or replace them with behavior tests.
7. **Parallel file conflicts.** Task 005 edits about 60 stub files, including `database-mysql`/`database-pgsql` test stubs (`ProbeRecordingConnection`, `MySqlIntrospectorTest`, `Query/*Connection`), while 003/004 run concurrently in the same test directories. 005 now depends on 003 and 004. Tasks 006 and 007 run in parallel and can both touch `packages/database/tests/Feature/*`, so ownership is assigned: 007 owns `DatabaseTestHelperTest`, the DataMigration tests and `testing`'s `TestDatabaseTest`; 006 owns the rest.
8. **Task 007 misses `packages/testing`.** `TestDatabase` delegates `seedTable()`/`getTableRowCount()` to `DatabaseTestHelper`; `packages/testing/tests/Feature/Database/TestDatabaseTest.php` asserts the SQL.
9. **Task 009 is too large.** It covers two drivers, reserved-word round-trips, DataMigration, DatabaseTestHelper, delimiter DDL and PermissionRepository. The PermissionRepository part is now split out (task 011). The fixed `permissions` table name needs `DROP TABLE IF EXISTS` (quoted) before and after each test.
10. **PostgreSQL behavior change for explicit mixed-case names (task 010).** Before this change, `Repository` SQL was unquoted and folded to lowercase. Tables created by hand-written, unquoted migrations with an explicit `#[Column(name: 'createdAt')]` or `#[Table('Users')]` will stop matching on PostgreSQL. Add an upgrade note to the docs, along with the `ConnectionInterface` break for third-party drivers.

## Minor (Nice to address)

- The generators did not split on `.` before; with the shared rule, a dotted column, index or constraint name in DDL becomes two identifiers. No name the framework generates contains a dot today (`fk_{table}_{col}` would only if the table name did).
- SQLite treats an unmatched `"name"` as a string literal (legacy DQS), so SQLite-backed stub tests can pass with a wrong column name. Assert the SQL text where it matters.
- `findBy`/`findOneBy`/`existsBy` fall back to the raw criteria key when it isn't a mapped property; a caller passing an expression key (e.g. `LOWER(email)`) now breaks loudly. That is arguably correct but undocumented.
- `TruncateDatabase` (testing) still inline-quotes with backticks; it could use `quoteIdentifier()` (out of scope per plan).
- The plan marks 001/002 `pending`, but `MySqlIdentifier`/`PgSqlIdentifier` already exist in the worktree. Make sure the orchestrator does not redo them.

## Questions for the Team

- Should DDL generators split on `.`, or quote the whole name as one identifier (schema-qualified tables are not a feature today)?
- Is extending the MariaDB CI step to `packages/admin-auth/tests/Integration/MySql` acceptable, or would you rather a dev dependency from the drivers on admin-auth?
