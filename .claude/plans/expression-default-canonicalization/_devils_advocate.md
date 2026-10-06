# Devil's Advocate Review: expression-default-canonicalization

Note: the worktree already has Task 001 (interface `matchesStoredDefault(string $table, string $column, Expression $expression): bool`, `Expression::unwrap()`, `MigrationException::rejectedDefaultExpression($table, $column, $expression, $reason)`) and most of Task 002 (`ExpressionDefaultCanonicalizer::canonicalize(array $entitySchema, array $databaseSchema): array`, constructed with `IntrospectorInterface`). The findings below are checked against that code.

## Critical (Must fix before building)

1. **Tasks 004/005: the integration tests need the canonicalizer (Task 002), but the tasks only depend on 001.** The "diffs as empty right after creation" integration tests cannot pass with `new DiffCalculator()->calculate()` alone (that is what the existing `SchemaDiffSettlesTest` files call). Fix: both tasks depend on 002, and the integration tests run `new ExpressionDefaultCanonicalizer($introspector)->canonicalize($entity, $database)` before `DiffCalculator::calculate()`.

2. **Task 004: `PgSqlIntrospector` only has `ConnectionInterface`, which has no `beginTransaction()`/`rollback()`.** Those live on `TransactionInterface`, which `PgSqlConnection` also implements. A new constructor parameter would break 28 `new PgSqlIntrospector(...)` calls in `PgSqlIntrospectorTest` plus the integration tests. Raw `BEGIN`/`ROLLBACK` SQL would put `PgSqlConnection`'s transaction-level and savepoint tracking out of sync. Fix: use `$this->connection instanceof TransactionInterface` and call `beginTransaction()`/`rollback()`. Without it, fall back to create plus `DROP TABLE IF EXISTS pg_temp.<probe>` in `finally`.

3. **Task 004: the temporary table lives in `pg_temp_N`, not in `$this->schema`.** If the worker copies `getColumns()`'s query (`table_schema = ?` bound to `public`), the probe column is never found. Fix: read the probe's default with `table_schema = (SELECT nspname FROM pg_namespace WHERE oid = pg_my_temp_schema())` (or from `pg_attrdef` filtered on `pg_my_temp_schema()`).

## Important (Should fix before building)

4. **Task 004/005: the introspected `Column::$default` cannot be the comparison side.** PostgreSQL's `parseDefault()` strips a trailing `::type` cast. MySQL's `Column::$default` carries the `information_schema`-escaped text. `getColumns()` also does not fetch `format_type()` on PostgreSQL. So the matcher has to query the real column's raw default and native type itself. Fix: added this as an explicit requirement.

5. **Task 004/005: wrap only the failure of the CREATE statement.** Fix: only a `QueryException` thrown by `CREATE [TEMP|TEMPORARY] TABLE` becomes `rejectedDefaultExpression`, with the database's error message as `$reason` and the original exception chained. A `ConnectionException`, or a failure in `SHOW COLUMNS`/`SELECT`, must propagate unchanged and must not be reported as a rejected expression. A missing `CREATE TEMPORARY TABLES` privilege on MySQL also surfaces as this exception, so `$reason` has to carry the server message.

6. **Task 005: `MySqlGenerator::formatExpression()` is private, so the introspector cannot call it.** Fix: mirror its rule in the introspector: leave the CURRENT_TIMESTAMP/LOCALTIME/NOW family bare, and put anything else in parentheses unless it already is. Do not make the generator a dependency of the introspector.

7. **Task 005: the probe table name must not shadow a real table.** A MySQL temporary table hides a permanent table with the same name for the rest of the session, and a PostgreSQL temp table does the same through `search_path`. Fix: use a reserved name such as `marko_default_probe`, quote it with backticks, run `DROP TEMPORARY TABLE IF EXISTS` before `CREATE` and in `finally`, and branch on `isMariaDb()` for the unescape step (MariaDB text is not escaped).

8. **Task 003: adding a constructor parameter breaks 4 existing call sites, and the MigrateCommand test helper stubs the calculator.** The call sites are `tests/Command/Helpers.php:242`, plus `MigrateCommandTest.php` at lines 309, 695 and 742. `createMigrateDiffCalculator()` returns a canned `SchemaDiff`, so canonicalization has no visible effect through `createMigrateCommand()`. Fix: add a required `ExpressionDefaultCanonicalizer $expressionDefaultCanonicalizer` as the last constructor parameter of both commands (the container autowires it), update the 4 call sites, and write the MigrateCommand test with a real `DiffCalculator` (the pattern at line 695). Also cover the `reportDrift()` path, which calls `calculateDiff()` too.

## Minor (Nice to address)

- `Expression::isWrappedInOneParenthesisPair()` counts parentheses inside string literals. For example, `concat('(', x, ')')` is not wrapped, but `'(' ... ')'`-heavy text can confuse it. This is rare for defaults.
- MySQL charset introducers (`_utf8mb4`) follow the connection charset at creation time. The probe matches only if `db:diff` uses the same connection charset that created the column, which is normally the case.
- MySQL with `enforce_gtid_consistency` plus statement-based binlog can reject `CREATE TEMPORARY TABLE` inside a transaction. The commands do not open one, so this is not an issue today.
- MySQL unescaping: specify exactly which escapes are reversed (`\'` -> `'`, `\\` -> `\`) instead of using `stripslashes()`, which also reverses `\0` and `\"`.

## Questions for the Team

- In production, `db:migrate` runs `reportDrift()` after applying migrations. A probe failure there (a rejected expression, or a missing TEMP / CREATE TEMPORARY TABLES privilege for the deploy user) now makes the command return 1 after the migrations were applied. Should `reportDrift()` downgrade a `rejectedDefaultExpression` to a warning line instead of failing the deploy?
