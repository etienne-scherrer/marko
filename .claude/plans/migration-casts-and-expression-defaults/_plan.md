# Plan: Migration Casts and Expression Defaults

## Created
2026-10-06

## Status
completed

## Objective
Make PostgreSQL column type changes carry an explicit `USING` cast, and give column defaults an explicit expression/literal form (with a narrow, documented shortcut), so documented defaults like `gen_random_uuid()` generate working DDL on both drivers and round-trip through introspection without perpetual diffs.

## Related Issues
Closes #253

## Discovery Notes
- `PgSqlGenerator::generateModifyColumnIfChanged()` emits a bare `ALTER COLUMN ... TYPE`; PostgreSQL only applies an assignment cast there.
- Both generators decide "expression vs literal" from a fixed allow-list (`CURRENT_TIMESTAMP`, `CURRENT_DATE`, `CURRENT_TIME`, `NOW()`; MySQL also `NULL` and does prefix matching, so any string starting with `NULL`/`NOW()` was emitted raw).
- `PgSqlIntrospector::parseDefault()` returns expressions as bare strings, so they are indistinguishable from literals and a down migration that restores them quotes them. `MySqlIntrospector` passes `COLUMN_DEFAULT` straight through and ignores `EXTRA = DEFAULT_GENERATED`.
- `Column::defaultEquals()` uses `===`, so value objects would never compare equal.
- `#[Column(default: ...)]` is `mixed`; PHP allows `new` in attribute arguments, so `default: new Expression('gen_random_uuid()')` works without a new attribute parameter.
- `Repository::insert()` does not read back a non-auto-increment key, so a database-generated UUID is not put on the entity; the docs must say to assign it in PHP when the entity needs it.
- #252 (merged) added `Column::resolveAgainst()` and MySQL `MODIFY COLUMN` fidelity (`targetColumn()`), which this plan builds on.

## Scope

### In Scope
- `Marko\Database\Schema\Expression` and `Marko\Database\Schema\Literal` value objects; shortcut detection for bare keywords, precision forms and zero-argument function calls
- Column default equality that compares expressions by normalized SQL and treats a shortcut string as its expression
- PostgreSQL `USING "col"::TYPE` on type changes (up and down), dropping and restoring the default around the type change
- PostgreSQL and MySQL generators emitting expression defaults raw (MySQL wraps in parentheses where 8.0.13+ requires them)
- Both introspectors returning `Expression` for non-literal defaults and `Literal` for literal strings that would read as a shortcut
- Integration tests on real PostgreSQL and MySQL
- Docs: database.md, database-pgsql.md, database-mysql.md

### Out of Scope
- Reading database-generated primary keys back onto entities on insert
- Normalizing arbitrary expressions the server rewrites (e.g. `now() + interval '1 day'`); documented instead

## Success Criteria
- [x] A PostgreSQL type change emits `USING "col"::type` in up and down; varchar↔integer unit tests; populated-table integration test
- [x] `gen_random_uuid()` and explicit expressions emit unquoted defaults on PostgreSQL; `(UUID())` and `CURRENT_TIMESTAMP(6)` on MySQL
- [x] Introspected and entity expression defaults compare equal (no perpetual modify)
- [x] Function-looking strings can be stored as literals via `Literal`
- [x] Docs updated and the UUID examples work as written
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Expression and Literal value objects | - | completed |
| 002 | Column default equality for expressions and literals | 001 | completed |
| 003 | PostgreSQL generator: expression defaults and USING casts | 001, 002 | completed |
| 004 | MySQL generator: expression defaults | 001 | completed |
| 005 | PostgreSQL introspector: Expression and Literal defaults | 001 | completed |
| 006 | MySQL introspector: Expression and Literal defaults | 001 | completed |
| 007 | Integration tests on PostgreSQL and MySQL | 002, 003, 004, 005, 006 | completed |
| 008 | Documentation | 001, 002, 003, 004, 005, 006 | completed |

## Architecture Notes
- A plain string default stays the common case. It is an expression only when it matches the shortcut patterns (`Expression::isShortcut()`); anything else is quoted. `Expression` forces raw SQL, `Literal` forces a quoted string.
- Expression equality is case-insensitive and ignores whitespace and redundant outer parentheses, because servers report `NOW()` as `now()` and MySQL reports `(UUID())` as `uuid()`.
- Introspectors return a `Literal` only for literal strings that would otherwise read as a shortcut, so existing consumers still see plain strings.

## Risks & Mitigations
- Servers rewrite complex expressions: documented — write the expression the way the database reports it.
- MySQL servers without `DEFAULT_GENERATED` (5.7/MariaDB) report `CURRENT_TIMESTAMP` without a marker: keep timestamp keywords as plain strings there so they still read as expressions.
