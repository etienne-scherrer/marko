# Devil's Advocate Review: quote-raw-sql-identifiers

## Critical (Must fix before building)

1. **Task 003: the RecordingConnection fixture always quotes with `"`.** `packages/testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection.php::quoteIdentifier()` returns `'"' . ... . '"'` whatever `$driver` is. Once `truncateMySql()` goes through `quoteIdentifier()`, the existing mysql assertions in `TruncateDatabaseTest.php` (lines 66, 67, 86 expect `` TRUNCATE TABLE `shows` ``) fail, and the "quotes mysql with the connection's delimiter" test can't show a backtick. Fix: make the fixture driver-aware (a backtick when `driverName()` is `mysql`, a double quote otherwise, doubling the delimiter).

2. **Task 007: the detector isn't specified well enough, and the obvious version fails on day one.** `packages/*/src` contains about 38 string literals with backticks in exception messages and markdown renderers (devai, core, routing, admin), plus `IdentifierValidator.php:167` (`str_contains($expression, '`')`), `FtsQueryBuilder.php:54` (`'"'` FTS phrase quoting), and CSS/JS in `BasicHtmlFormatter` that contains the words "select" and "table". A case-insensitive keyword match flags these. Interpolated SQL (`"SELECT * FROM \"$t\""`) also doesn't tokenize as `T_CONSTANT_ENCAPSED_STRING`: the parts are `T_ENCAPSED_AND_WHITESPACE` and heredoc tokens. A scanner that only checks constant strings misses exactly the pattern being banned. Fix: write the detection rules into the task (listed in the task file).

## Important (Should fix before building)

3. **Task 004: `reserveNext()` passes `$this->table` to the query builder** (`DatabaseQueue.php:184`, `->table($this->table)`). The builder quotes it again, so handing it the quoted `table()` result double-quotes it. Only the raw statements (lines 105, 202, 274, 290, 301, 320, 336) may use the helper.
4. **Task 002 has the wrong pattern reference and leaves gotchas unstated.** The real pattern is `packages/session-database/tests/Integration/{MySql,PgSql}` (IntegrationDatabase fixture skip/require, `pest()->group('integration-services')`, table built through the driver generator, dropped in afterEach). The CI MariaDB steps list paths explicitly, so the directory layout must be fixed up front (`packages/search/tests/Integration/MySql`), and the path added to both MariaDB steps and the list in `tests/CiWorkflowTest.php`. PostgreSQL rejects `LIKE` on integer columns, so searchable `key`/`group` fields must be string columns. An integer `order` column can only be sorted or filtered on, never be searchable. On PostgreSQL the mixed-case test must assert the row key comes back as `displayName`.
5. **Tasks 004, 005, 006: existing exact-SQL assertions will break.** Each package's test doubles quote with `"`, so tests asserting the current bare SQL text need updating, and that work isn't in the task scope. Say so explicitly so the worker doesn't think the change caused a regression.

## Minor (Nice to address)

- Task 001 / `_plan.md` Discovery Notes: `packages/search/composer.json` already requires `marko/database` and has `marko/database-mysql`/`-pgsql` in require-dev. The "add the missing require" step is already done, so the worker only needs to check it.
- Task 006: `PermissionRepository` already quotes columns as well as tables. Quoting tables only in Role/AdminUser repositories is consistent with the plan's out-of-scope rule but inconsistent within the package.
- Tasks 001-008 all reference `RepositoryIdentifierQuotingTest` as their pattern, even where it doesn't fit (docs, guard test).

## Questions for the Team

- `packages/database/src/Migration/MigrationRepository.php` builds raw SQL against a fixed `migrations` table. This is the same kind of fixed-name site as `sessions`/`notifications`, but the plan neither includes nor excludes it. Should it be in scope?
- `packages/docs-fts` runs raw SQLite SQL through PDO, not `ConnectionInterface`, so it can't use `quoteIdentifier()`. It has no quote characters today. Should the guard exclude it explicitly, or is the SQL-statement heuristic enough?
