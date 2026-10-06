# Plan: Identifier Quoting

## Created
2026-10-06

## Status
completed

## Objective
Give every database driver one identifier-quoting rule (a per-driver class that wraps names in the driver's delimiter and doubles embedded delimiters), expose it through `ConnectionInterface::quoteIdentifier()`, and use it everywhere Marko emits a table or column name: SQL generators, query builders, introspectors, `Repository`, `DataMigration` and `DatabaseTestHelper`. This fixes `marko/admin-auth`'s `Permission` entity (`key`, `group` columns) on real MySQL, MariaDB and PostgreSQL.

## Related Issues
Closes #323

## Discovery Notes
- Query builders already quote correctly via `IdentifierValidator::escapeDelimiter()`; `MySqlGenerator::quote()` and ~20 inline `"\"$name\""` sites in `PgSqlGenerator` quote without escaping; `Repository` (12 sites + `"$column = ?"` + `RETURNING $pkColumn`), `DataMigration` and `DatabaseTestHelper` do not quote at all.
- `PgSqlIntrospector` has its own private `quoteIdentifier()`; `MySqlIntrospector` hard-codes backticks around a constant probe table name.
- `PermissionRepository::findByGroup()` hard-codes MySQL backticks.
- `admin-auth`'s own migrations are hand-written MySQL DDL (`AUTO_INCREMENT`, `INT UNSIGNED`, backticks), so the package cannot be *installed* on PostgreSQL through its migrations. That is outside #323 (quoting of emitted SQL); the PostgreSQL integration test creates the `permissions` table from the entity through `SchemaBuilder` + `PgSqlGenerator`, as `db:migrate` would for an entity-driven table. Called out in the PR as a follow-up.
- ~96 `ConnectionInterface` test stubs in ~60 files (same set #321 touched for `supportsReturning()`). Some stubs run real SQLite, which accepts ANSI double-quoted identifiers, so stubs quote with `"` and double embedded `"`.
- Integration tests live in `packages/database-{mysql,pgsql}/tests/Integration` in the `integration-services` group (the ticket says "destructive group", but `integration-destructive` is the clean-install group; `integration-services` is the real-database group). CI runs the MySQL driver directory a second time against MariaDB.
- No `pre-plan`/`post-plan` hooks enrolled.

## Scope

### In Scope
- `MySqlIdentifier` / `PgSqlIdentifier` (`src/Sql/`) with static `quote()`.
- Generators, query builders and introspectors of both drivers delegate to them; PostgreSQL `DO` block uses a dollar-quote tag that cannot occur in its body.
- `ConnectionInterface::quoteIdentifier()` in all three drivers and every test stub.
- Quoting in `Repository`, `DataMigration`, `DatabaseTestHelper`.
- `PermissionRepository::findByGroup()` uses `findBy()`.
- Real-database integration tests (MySQL, MariaDB, PostgreSQL) for a reserved-word entity and for `PermissionRepository`.
- Docs: `database.md`, `database-mysql.md`, `database-pgsql.md`.

### Out of Scope
- Rewriting `admin-auth` migrations to be driver-neutral.
- Raw SQL in other packages (`queue-database`, `search`, `notification`, `testing`'s `TruncateDatabase`) — not listed by the ticket; they use fixed, non-reserved names.
- Rejecting delimiter characters at schema-declaration time (#315 territory).

## Success Criteria
- [x] One quoting function per driver; generators, query builders (and introspectors) use it; no inline backtick / `\"` identifier quoting left in them
- [x] Delimiter in a name is escaped in generated DDL; DO block safe for names containing `$`
- [x] Repository, DataMigration, DatabaseTestHelper quote every table and column name, including RETURNING
- [x] PermissionRepository has no driver-specific syntax; permissions save and load on MySQL, MariaDB and PostgreSQL
- [x] Integration tests for reserved-word columns on MySQL, MariaDB, PostgreSQL
- [x] Docs updated
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | MySqlIdentifier quoting class | - | completed |
| 002 | PgSqlIdentifier quoting class | - | completed |
| 003 | MySQL generator, query builder, introspector use MySqlIdentifier | 001 | completed |
| 004 | PostgreSQL generator, query builder, introspector use PgSqlIdentifier; safe DO tag | 002 | completed |
| 005 | ConnectionInterface::quoteIdentifier() in drivers and all stubs | 001, 002, 003, 004 | completed |
| 006 | Repository quotes every identifier (all packages' tests, incl. PHPUnit mocks) | 005 | completed |
| 007 | DataMigration and DatabaseTestHelper quote identifiers (incl. testing's TestDatabaseTest) | 005 | completed |
| 008 | PermissionRepository::findByGroup() without backticks | 006 | completed |
| 009 | Driver integration tests: reserved-word entity, DataMigration, DatabaseTestHelper, delimiter DDL | 003, 004, 006, 007 | completed |
| 010 | Docs (incl. upgrade note) | 001-009, 011 | completed |
| 011 | PermissionRepository integration tests in admin-auth (MySQL/MariaDB/PostgreSQL) + CI MariaDB step | 003, 004, 006, 008 | completed |

## Architecture Notes
- Static `quote()` mirrors `IdentifierValidator`'s static helpers; the extensibility point is the connection's `quoteIdentifier()` (a Preference on the connection changes what `Repository` emits).
- `table.column` is split on `.` and each part quoted, matching the query builders' existing behavior.
- `ReadWriteConnection::quoteIdentifier()` delegates to the write connection (no live connection needed; quoting is pure).

## Risks & Mitigations
- Many unit tests assert exact SQL strings: update them to the quoted form (the stubs quote with `"`), which also makes them prove quoting.
- Exception translators parse table names out of SQL with optional quotes: covered by their existing patterns; verify with tests.
- Parallel PRs (#304, #315, #325) touch the same files: rebase on `origin/develop` before finishing.
- PHPUnit `createMock()`/`createStub()` connections return `''` from an unconfigured `quoteIdentifier()`: tasks 006/007 configure it on every mock that reaches Repository/DataMigration.
- PermissionRepository integration tests live in `admin-auth/tests/Integration/{MySql,PgSql}` (drivers added to admin-auth `require-dev`); the CI MariaDB step is extended to run the MySql directory (task 011).
- PostgreSQL: quoting makes explicit mixed-case names case-sensitive; documented as an upgrade note (task 010).
