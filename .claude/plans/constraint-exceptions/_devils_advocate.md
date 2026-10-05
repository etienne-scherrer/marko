# Devil's Advocate Review: constraint-exceptions

Note: `QueryException`, `ConstraintViolationException`, `UniqueConstraintViolationException`, `ForeignKeyConstraintViolationException` and `PgSqlExceptionTranslatorTest` already exist in the worktree (001/002 in progress). The findings below are checked against that code.

## Critical (Must fix before building)

1. **Task 006: the new integration test files will fatal with "Cannot redeclare function".** `packages/database-pgsql/tests/Integration/SharedConnectionTransactionTest.php` declares `pgsqlIntegrationConfig()`, `pgsqlRowCount()` and `const PGSQL_SKIP_REASON` in namespace `Marko\Database\PgSql\Tests\Integration`. MySQL does the same with `mysqlIntegrationConfig()`, `mysqlRowCount()` and `MYSQL_SKIP_REASON`. A second file in the same namespace that copies the pattern breaks the whole suite. Fix: use unique helper names, or move the shared helpers into a `Helpers.php` loaded once.
2. **Task 006: table names will collide across parallel workers.** The existing file drops and recreates `shared_accounts` and `shared_audit_entries` in `beforeEach`. Under `--parallel`, a new file that reuses those tables races with it. Fix: use dedicated tables (for example `constraint_users` and `constraint_posts` with an FK), and drop the child table first.
3. **Task 007: adding a fixture migration breaks `ServicesTest`.** `ServicesTest` asserts `Applied 5 schema migration(s).` and an exact table list. The fixture has no unique column today (`authors` is just id and name). Fix: create the unique index inside the test with `ConnectionInterface::execute()`, or update `ServicesTest` in the same task.
4. **Task 007: the todo flip has rules the task does not mention.** The replacement test must live in `tests/Integration/App/*Test.php` and be tagged `->issue(177)`, because `HarnessTest` regex-scans those files. The `->todo` at the end of `KnownGapsTest.php` must also be deleted. Neither step is in the task.

## Important (Should fix before building)

5. **Task 001: redaction corrupts messages when bindings are short or numeric strings.** `QueryException::redact()` runs `str_replace` on every non-empty string binding. Route IDs often arrive as strings like `"1"`, and replacing those mangles `SQLSTATE[42P01]`. A binding such as `"a"` mangles every word in the message. Fix: always redact quoted forms (`'v'`, `"v"`), and redact bare occurrences only when the value is long enough (4 or more characters). Added as a test requirement.
6. **Tasks 001/002/003: the shared contract is undocumented.** Task 003 runs in parallel with 002 and must match `translate(PDOException $e, string $sql, array $bindings): QueryException`, the `fromDriverError(...)` factory shape, and the rule that `table()` is the table that owns the constraint (the child table for FK violations, as the pgsql test already pins). Without this, MySQL and pgsql drift apart.
7. **Task 003: driver-code coverage gaps.**
   - There are no tests for MariaDB 4025 or legacy 1216/1217, even though the context lists them.
   - MySQL 1364 (`Field 'x' doesn't have a default value`, SQLSTATE HY000) is what strict mode raises when a NOT NULL column is omitted. PostgreSQL raises 23502 for the same mistake, so without mapping 1364 the hierarchy is not driver-agnostic.
   - 1062 has two key formats: `'table.key'` on MySQL 8.0.19+ and `'key'` on 5.7/MariaDB. The duplicate value can itself contain `' for key '`, so anchor the parse at the end of the message.
   - pgsql reads the table from the SQL when the server omits it. MySQL needs the same fallback for parity.
8. **Tasks 004/005: wiring details are not specified.**
   - Catch only `PDOException`. `ConnectionException` from `ensureConnected()` and `invalidArrayBinding` must pass through untouched.
   - The statement gets its SQL from `PDOStatement::$queryString`.
   - The `*Statement` constructor needs an optional translator parameter. `PgSqlConnectionTest` and `MySqlConnectionTest` construct statements and connections directly.
   - `Connection::prepare()` can also throw (MySQL with native prepares).
   - Unit tests use the sqlite-backed PDO from the existing tests. sqlite constraint errors are SQLSTATE 23000/code 19, so the pgsql translator falls back to `QueryException`. Assert that, or inject a stub translator.
9. **Tasks 006/008: PostgreSQL aborts the transaction after a violation.** Every later statement in that transaction fails with 25P02 until rollback. A "catch duplicate and continue" example inside `transaction()` will not work on pgsql. Tests must not assert through an aborted transaction, and the docs must warn about it (savepoints are #176).
10. **Task 008: undocumented BC break.** Code that did `catch (PDOException)` around `query()`, `execute()` or the repository no longer catches anything, including deadlock-retry loops. The docs need an upgrade note: catch `QueryException`, inspect `sqlState()` or `getPrevious()`.
11. **Task 006: MySQL is not in `tests/Integration/compose.yml` or CI.** The MySQL tests skip everywhere unless a developer has a local server, so the regexes are only proven against fixture strings. The worker must record in Implementation Notes whether MySQL was actually exercised.
12. **Task 007: route placement and method.** `IntegrationController` is a merge hotspot (#171/#173 also edit it). Put the route on a new fixture controller and inject `AuthorRepository`. Use GET to avoid the CSRF middleware.

## Minor (Nice to address)

- `PgSqlConnectionFactory`/`MySqlConnectionFactory` call `new *Connection($config, $charset)`, so a Preference on the translator does not reach factory-built connections (read replicas). Only container-autowired connections honour it.
- Composite or expression unique keys (`Key (a, b)=`, `Key (lower(email::text))=`) are a column-extraction edge. Return `null` rather than a malformed name.
- `Migrator`/`DataMigrator` catch `Throwable` and print `getMessage()`. Their output changes from the raw driver text to `Query failed: ...` / `Unique constraint ...`. This is probably fine, but snapshot tests may notice.
- Stack-trace arguments still carry bindings unless `zend.exception_ignore_args` is on. This is pre-existing behaviour, unchanged.

## Questions for the Team

- For a composite unique key, should `column()` be `null`, the first column, or `"a, b"`?
- Should deadlocks (40001/40P01, MySQL 1213) get their own type in a follow-up, now that `catch (PDOException)` retry loops stop working?
