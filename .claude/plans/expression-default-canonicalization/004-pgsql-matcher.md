# Task 004: PostgreSQL matcher implementation

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
`PgSqlIntrospector` implements `ExpressionDefaultMatcherInterface`: inside a transaction that is always rolled back, create a temp table with one column of the real column's type (`format_type()`) and `DEFAULT <expr>`, read its `column_default` with the same `information_schema.columns` query shape, and compare with the real column's `column_default`.

## Context
- Related files: packages/database-pgsql/src/Introspection/PgSqlIntrospector.php, tests/Introspection/PgSqlIntrospectorTest.php, tests/Integration/ColumnCastsAndExpressionDefaultsTest.php

## Requirements (Test Descriptions)
- [x] `it reports a match when the probe stores the same default text as the column`
- [x] `it reports no match when the probe stores a different default text`
- [x] `it rolls back the probe transaction`
- [x] `it throws a MigrationException naming the column and expression when PostgreSQL rejects the expression`
- [x] `it reads the probe column from the session's temporary schema`
- [x] `it lets a connection failure propagate without reporting a rejected expression`
- [x] integration: `it diffs an interval arithmetic expression default as empty right after creation`
- [x] integration: `it still diffs a changed interval expression and sets the new default`
- [x] integration: `it fails at diff time for an expression PostgreSQL rejects`
- [x] integration: `it leaves no probe table behind and keeps the connection usable after a rejected expression`

## Implementation Constraints (from devil's advocate review)
- `matchesStoredDefault(string $table, string $column, Expression $expression): bool` is already declared in `packages/database/src/Introspection/ExpressionDefaultMatcherInterface.php`.
- Transactions: `PgSqlIntrospector` holds `ConnectionInterface`, which has no transaction methods. Do NOT add a constructor parameter (28+ existing `new PgSqlIntrospector(...)` calls). Use `$this->connection instanceof TransactionInterface` and call `beginTransaction()`/`rollback()` (the connection uses a savepoint when nested). Never send raw `BEGIN`/`ROLLBACK`, because that puts the connection's transaction-level tracking out of sync. Without `TransactionInterface`, create the table and `DROP TABLE IF EXISTS pg_temp.<probe>` in `finally`.
- Real column: query its raw `column_default` and `format_type(a.atttypid, a.atttypmod)` yourself (`pg_attribute` joined to `pg_class`/`pg_namespace` with `nspname = $this->schema`). `Column::$default` is not usable because `parseDefault()` strips a trailing `::type` cast, and `getColumns()` does not fetch `format_type`.
- Probe column: a reserved table name (e.g. `marko_default_probe`). Read its `column_default` with `table_schema = (SELECT nspname FROM pg_namespace WHERE oid = pg_my_temp_schema())`, not `$this->schema`, because temp tables live in `pg_temp_N`.
- Errors: only a `QueryException` from the `CREATE TEMP TABLE` statement becomes `MigrationException::rejectedDefaultExpression($table, $column, $expression->sql, $e->getMessage())`. A `ConnectionException`, or a failure of any other query, propagates unchanged.
- Integration tests run `new ExpressionDefaultCanonicalizer($introspector)->canonicalize($entity, $database)` before `new DiffCalculator()->calculate(...)` (see `tests/Integration/SchemaDiffSettlesTest.php` for the existing diff helper).

## Acceptance Criteria
- All requirements have passing tests (integration against PostgreSQL 17)

## Implementation Notes
Implemented with strict TDD; see the PR description for the design notes.
